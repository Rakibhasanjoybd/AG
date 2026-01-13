<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class AuthController extends ApiController
{
    /**
     * Register a new user
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|max:100',
            'mobile_code' => 'required|string|max:5',
            'mobile' => 'required|string|max:20|unique:users,mobile',
            'password' => 'required|string|min:6|confirmed',
            'withdrawal_pin' => 'required|string|digits:4',
            'country_code' => 'required|string|max:5',
            'referral_code' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        try {
            $result = DB::transaction(function () use ($request) {
                $general = gs();

                // Get country data
                $countryData = json_decode(file_get_contents(resource_path('views/partials/country.json')), true);
                $countryName = $countryData[$request->country_code]['country'] ?? null;

                // Find referrer
                $referUser = null;
                if ($request->referral_code) {
                    $referUser = User::where('referral_code', $request->referral_code)
                                    ->orWhere('username', $request->referral_code)
                                    ->first();
                }

                // Generate unique data
                $userReferralCode = $this->generateUniqueReferralCode();
                $autoUsername = 'user' . str_replace(['+', '-', ' '], '', $request->mobile);
                $autoEmail = $request->mobile_code . $request->mobile . '@agco.app';

                // Create user
                $user = new User();
                $user->fullname = trim($request->fullname);
                $user->firstname = explode(' ', trim($request->fullname))[0] ?? '';
                $user->lastname = explode(' ', trim($request->fullname), 2)[1] ?? '';
                $user->email = $autoEmail;
                $user->password = Hash::make($request->password);
                $user->withdrawal_pin = Hash::make($request->withdrawal_pin);
                $user->username = $autoUsername;
                $user->referral_code = $userReferralCode;
                $user->ref_by = $referUser ? $referUser->id : 0;
                $user->country_code = $request->country_code;
                $user->mobile = $request->mobile_code . $request->mobile;
                $user->address = (object)[
                    'address' => '',
                    'state' => '',
                    'zip' => '',
                    'country' => $countryName,
                    'city' => ''
                ];
                $user->status = 1;
                $user->kv = $general->kv ? 0 : 1;
                $user->ev = $general->ev ? 0 : 1;
                $user->sv = $general->sv ? 0 : 1;
                $user->ts = 0;
                $user->tv = 1;
                $user->reg_step = 1;
                $user->save();

                // Two-Side Referral Commission on Registration
                // Referrer gets X Tk, Referred user gets X Tk (only on account opening)
                if ($referUser && @$general->referral_signup_commission) {
                    $referrerAmount = $general->referral_signup_referrer_amount ?? 10;
                    $referredAmount = $general->referral_signup_referred_amount ?? 10;
                    $trx = getTrx();

                    // Give commission to Referrer
                    $referrer = User::where('id', $referUser->id)->lockForUpdate()->first();
                    if ($referrer) {
                        $referrer->balance += $referrerAmount;
                        $referrer->save();

                        $transaction = new \App\Models\Transaction();
                        $transaction->user_id = $referrer->id;
                        $transaction->amount = $referrerAmount;
                        $transaction->post_balance = $referrer->balance;
                        $transaction->charge = 0;
                        $transaction->trx_type = '+';
                        $transaction->details = 'Referral signup commission from ' . $user->username;
                        $transaction->remark = 'referral_signup_commission';
                        $transaction->trx = $trx;
                        $transaction->save();

                        // Notify referrer
                        try {
                            notify($referrer, 'REFERRAL_SIGNUP_COMMISSION', [
                                'amount' => showAmount($referrerAmount),
                                'post_balance' => showAmount($referrer->balance),
                                'referred_user' => $user->username,
                                'trx' => $trx,
                            ]);
                        } catch (\Exception $e) {
                            Log::warning("Failed to send referral signup notification: " . $e->getMessage());
                        }
                    }

                    // Give bonus to Referred User (new user)
                    $user = User::where('id', $user->id)->lockForUpdate()->firstOrFail();
                    $user->balance += $referredAmount;
                    $user->save();

                    $transaction = new \App\Models\Transaction();
                    $transaction->user_id = $user->id;
                    $transaction->amount = $referredAmount;
                    $transaction->post_balance = $user->balance;
                    $transaction->charge = 0;
                    $transaction->trx_type = '+';
                    $transaction->details = 'Referral signup bonus (referred by ' . $referUser->username . ')';
                    $transaction->remark = 'referral_signup_bonus';
                    $transaction->trx = $trx . '_REF';
                    $transaction->save();
                }

                // Apply registration bonus
                if ($general->registration_bonus > 0) {
                    $user = User::where('id', $user->id)->lockForUpdate()->firstOrFail();
                    $user->balance += $general->registration_bonus;
                    $user->save();

                    // Create transaction record
                    $transaction = new \App\Models\Transaction();
                    $transaction->user_id = $user->id;
                    $transaction->amount = $general->registration_bonus;
                    $transaction->post_balance = $user->balance;
                    $transaction->charge = 0;
                    $transaction->trx_type = '+';
                    $transaction->details = 'Registration Bonus';
                    $transaction->remark = 'registration_bonus';
                    $transaction->trx = getTrx();
                    $transaction->save();
                }

                // Apply default plan
                $plan = \App\Models\Plan::where('status', 1)->find($general->default_plan);
                if ($plan) {
                    $user->daily_limit = $plan->daily_limit;
                    $user->expire_date = now()->addDays($plan->validity);
                    $user->plan_id = $plan->id;
                    if (User::usersTableHasColumn('anytime_withdraw_used')) {
                        $user->anytime_withdraw_used = 0;
                    }
                    if (User::usersTableHasColumn('last_weekly_withdraw')) {
                        $user->last_weekly_withdraw = null;
                    }
                    if (User::usersTableHasColumn('plan_purchase_date')) {
                        $user->plan_purchase_date = now();
                    }
                    $user->save();
                }

                // Create admin notification
                $adminNotification = new AdminNotification();
                $adminNotification->user_id = $user->id;
                $adminNotification->title = 'New member registered';
                $adminNotification->click_url = urlPath('admin.users.detail', $user->id);
                $adminNotification->save();

                // Create login log
                $this->createLoginLog($user);

                // Generate JWT token
                $token = JWTAuth::fromUser($user);

                return [
                    'user' => $user->fresh(),
                    'token' => $token,
                ];
            });

            return $this->success($result, 'Registration successful', 201);

        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return $this->error('Registration failed. Please try again.', 500);
        }
    }

    /**
     * Login user and return token
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|max:20',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $credentials = [
            'mobile' => $request->mobile,
            'password' => $request->password,
        ];

        // Check if user exists
        $user = User::where('mobile', $request->mobile)->first();
        if (!$user) {
            $this->logSecurityEvent('Login attempt with non-existent mobile', [
                'mobile' => $request->mobile,
            ]);
            return $this->error('Invalid credentials', 401);
        }

        // Check user status
        if ($user->status == 0) {
            return $this->error('Your account has been banned', 403);
        }

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                $this->logSecurityEvent('Failed login attempt', [
                    'user_id' => $user->id,
                    'mobile' => $request->mobile,
                ]);
                return $this->error('Invalid credentials', 401);
            }

            // Create login log
            $this->createLoginLog($user);

            // Get user with relationships
            $user = $user->fresh(['plan', 'referrals']);

            return $this->success([
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
                'user' => $user,
            ], 'Login successful');

        } catch (\Exception $e) {
            Log::error('Login error', [
                'error' => $e->getMessage(),
                'mobile' => $request->mobile,
            ]);

            return $this->error('Login failed. Please try again.', 500);
        }
    }

    /**
     * Logout user (Invalidate token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            auth('api')->logout();
            return $this->success(null, 'Successfully logged out');
        } catch (\Exception $e) {
            return $this->error('Failed to logout', 500);
        }
    }

    /**
     * Refresh token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        try {
            $token = auth('api')->refresh();
            return $this->success([
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
            ], 'Token refreshed successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to refresh token', 500);
        }
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        if (!$this->user) {
            return $this->unauthorized();
        }

        $user = $this->user->fresh(['plan', 'referrals', 'transactions']);

        return $this->success($user);
    }

    /**
     * Create login log for user
     *
     * @param \App\Models\User $user
     * @return void
     */
    protected function createLoginLog(User $user)
    {
        $ip = getRealIP();
        $exist = UserLogin::where('user_ip', $ip)->first();

        $userLogin = new UserLogin();

        if ($exist) {
            $userLogin->longitude = $exist->longitude;
            $userLogin->latitude = $exist->latitude;
            $userLogin->city = $exist->city;
            $userLogin->country_code = $exist->country_code;
            $userLogin->country = $exist->country;
        } else {
            $info = json_decode(json_encode(getIpInfo()), true);
            $userLogin->longitude = @implode(',', $info['long']);
            $userLogin->latitude = @implode(',', $info['lat']);
            $userLogin->city = @implode(',', $info['city']);
            $userLogin->country_code = @implode(',', $info['code']);
            $userLogin->country = @implode(',', $info['country']);
        }

        $userAgent = osBrowser();
        $userLogin->user_id = $user->id;
        $userLogin->user_ip = $ip;
        $userLogin->browser = @$userAgent['browser'];
        $userLogin->os = @$userAgent['os_platform'];
        $userLogin->created_at = now();
        $userLogin->save();
    }

    /**
     * Generate unique referral code
     *
     * @return string
     */
    protected function generateUniqueReferralCode()
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 5));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }
}
