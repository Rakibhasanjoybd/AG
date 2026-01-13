<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\User;
use App\Models\UserLogin;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('checkUser');
        $this->middleware('registration.status')->except('registrationNotAllowed');
        $this->activeTemplate = activeTemplate();
    }

    public function showRegistrationForm()
    {
        $pageTitle = "Register";
        $info = json_decode(json_encode(getIpInfo()), true);
        $mobileCode = @implode(',', $info['code']);
        $detectedCountry = @implode(',', $info['code']); // Auto-detect country from IP
        $countries = json_decode(file_get_contents(resource_path('views/partials/country.json')));

        $general = gs();
        $allowedCountryCodes = [];
        $forcedCountryCode = '';

        if (@$general->country_restriction) {
            $allowedCountryCodes = is_array($general->allowed_countries) ? array_values($general->allowed_countries) : [];
            $forcedCountryCode = (string)@$general->forced_country_code;

            if ($forcedCountryCode) {
                $allowedCountryCodes = [$forcedCountryCode];
                $detectedCountry = $forcedCountryCode;
            }

            if ($allowedCountryCodes) {
                $countries = collect($countries)->only($allowedCountryCodes)->all();

                if ($detectedCountry && !in_array($detectedCountry, $allowedCountryCodes)) {
                    $detectedCountry = (string)($allowedCountryCodes[0] ?? '');
                }
            }
        }

        // Handle referral code from URL
        if (request()->get('ref')) {
            session()->put('reference', request()->get('ref'));
        }

        return view($this->activeTemplate . 'user.auth.register', compact('pageTitle','mobileCode','countries','detectedCountry','allowedCountryCodes','forcedCountryCode'));
    }


    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $general = gs();
        $passwordValidation = Password::min(6);
        if ($general->secure_password) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }
        $agree = 'nullable';
        if ($general->agree) {
            $agree = 'required';
        }
        $countryData = json_decode(file_get_contents(resource_path('views/partials/country.json')), true);
        $countryCodesArr = array_keys($countryData);
        $mobileCodes = implode(',',array_column($countryData, 'dial_code'));
        $countries = implode(',',array_column($countryData, 'country'));

        if (@$general->country_restriction) {
            $allowed = is_array($general->allowed_countries) ? array_values($general->allowed_countries) : [];
            $forced = (string)@$general->forced_country_code;

            if ($forced) {
                $allowed = [$forced];
            }

            if ($allowed) {
                $countryCodesArr = array_values(array_intersect($countryCodesArr, $allowed));
            }
        }

        $countryCodes = implode(',', $countryCodesArr);
        $validate = Validator::make($data, [
            'fullname' => 'required|string|max:100',
            'mobile' => 'required|regex:/^([0-9]*)$/|min:10|max:10',
            'password' => ['required', $passwordValidation],
            'withdrawal_pin' => 'required|digits_between:4,6',
            'referral_code' => 'nullable|string|max:10',
            'captcha' => 'sometimes|required',
            'mobile_code' => 'required|in:'.$mobileCodes,
            'country_code' => 'required|in:'.$countryCodes,
            'country' => 'required|in:'.$countries,
            'agree' => $agree
        ]);

        $validate->after(function ($validator) use ($data, $countryData) {
            $countryCode = $data['country_code'] ?? null;
            if (!$countryCode || !isset($countryData[$countryCode])) {
                return;
            }

            $expectedDialCode = $countryData[$countryCode]['dial_code'] ?? null;
            if ($expectedDialCode && isset($data['mobile_code']) && (string)$data['mobile_code'] !== (string)$expectedDialCode) {
                $validator->errors()->add('mobile_code', 'Invalid mobile code for selected country');
            }

            $expectedCountry = $countryData[$countryCode]['country'] ?? null;
            if ($expectedCountry && isset($data['country']) && (string)$data['country'] !== (string)$expectedCountry) {
                $validator->errors()->add('country', 'Invalid country for selected country code');
            }
        });

        return $validate;

    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $request->session()->regenerateToken();

        if(!verifyCaptcha()){
            $notify[] = ['error','Invalid captcha provided'];
            return back()->withNotify($notify);
        }

        $exist = User::where('mobile',$request->mobile_code.$request->mobile)->first();
        if ($exist) {
            $notify[] = ['error', 'The mobile number already exists'];
            return back()->withNotify($notify)->withInput();
        }

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $general = gs();

        $countryData = json_decode(file_get_contents(resource_path('views/partials/country.json')), true);
        $countryName = null;
        if (!empty($data['country_code']) && isset($countryData[$data['country_code']])) {
            $countryName = $countryData[$data['country_code']]['country'] ?? null;
        }
        if (!$countryName) {
            $countryName = $data['country'] ?? null;
        }

        // Find referrer by referral_code (new 4-5 digit code) or username (legacy)
        $referUser = null;
        $referralInput = $data['referral_code'] ?? session()->get('reference');

        if ($referralInput) {
            // First try to find by new referral_code
            $referUser = User::where('referral_code', $referralInput)->first();
            // If not found, try by username (legacy support)
            if (!$referUser) {
                $referUser = User::where('username', $referralInput)->first();
            }
        }

        // Generate unique referral code (4-5 digit)
        $userReferralCode = $this->generateUniqueReferralCode();

        // Auto-generate username from mobile number
        $autoUsername = 'user' . $data['mobile'];

        // Auto-generate email from mobile number
        $autoEmail = $data['mobile_code'] . $data['mobile'] . '@agco.app';

        //User Create
        $user = new User();
        $user->fullname = trim($data['fullname']);
        $user->firstname = explode(' ', trim($data['fullname']))[0] ?? '';
        $user->lastname = explode(' ', trim($data['fullname']), 2)[1] ?? '';
        $user->email = $autoEmail;
        $user->password = Hash::make($data['password']);
        $user->withdrawal_pin = Hash::make($data['withdrawal_pin']);
        $user->username = $autoUsername;
        $user->referral_code = $userReferralCode;
        $user->ref_by = $referUser ? $referUser->id : 0;
        $user->country_code = $data['country_code'];
        $user->mobile = $data['mobile_code'].$data['mobile'];
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
        $user->reg_step = 1; // Auto-complete registration step - skip user data page
        $user->save();

        // Two-Side Referral Commission on Registration
        // Referrer gets X Tk, Referred user gets X Tk (only on account opening)
        if ($referUser && @$general->referral_signup_commission) {
            $referrerAmount = $general->referral_signup_referrer_amount ?? 10;
            $referredAmount = $general->referral_signup_referred_amount ?? 10;

            DB::transaction(function () use ($referUser, &$user, $referrerAmount, $referredAmount) {
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
            });
        }

        // Apply registration bonus immediately
        if ($general->registration_bonus > 0) {
            $bonusAmount = $general->registration_bonus;

            DB::transaction(function () use (&$user, $bonusAmount) {
                $user = User::where('id', $user->id)->lockForUpdate()->firstOrFail();
                $user->balance += $bonusAmount;
                $user->save();

                $transaction = new \App\Models\Transaction();
                $transaction->user_id = $user->id;
                $transaction->amount = $bonusAmount;
                $transaction->post_balance = $user->balance;
                $transaction->charge = 0;
                $transaction->trx_type = '+';
                $transaction->details = 'Registration Bonus';
                $transaction->remark = 'registration_bonus';
                $transaction->trx = getTrx();
                $transaction->save();
            });
        }

        // Apply default plan if exists
        $plan = \App\Models\Plan::where('status', 1)->find($general->default_plan);
        if ($plan) {
            $user->daily_limit = $plan->daily_limit;
            $user->expire_date = now()->addDays($plan->validity);
            $user->plan_id = $plan->id;
            // Initialize withdrawal counters
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

        // Clear referral session
        session()->forget('reference');

        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->title = 'New member registered';
        $adminNotification->click_url = urlPath('admin.users.detail',$user->id);
        $adminNotification->save();


        //Login Log Create
        $ip = getRealIP();
        $exist = UserLogin::where('user_ip',$ip)->first();
        $userLogin = new UserLogin();

        //Check exist or not
        if ($exist) {
            $userLogin->longitude =  $exist->longitude;
            $userLogin->latitude =  $exist->latitude;
            $userLogin->city =  $exist->city;
            $userLogin->country_code = $exist->country_code;
            $userLogin->country =  $exist->country;
        }else{
            $info = json_decode(json_encode(getIpInfo()), true);
            $userLogin->longitude =  @implode(',',$info['long']);
            $userLogin->latitude =  @implode(',',$info['lat']);
            $userLogin->city =  @implode(',',$info['city']);
            $userLogin->country_code = @implode(',',$info['code']);
            $userLogin->country =  @implode(',', $info['country']);
        }

        $userAgent = osBrowser();
        $userLogin->user_id = $user->id;
        $userLogin->user_ip =  $ip;

        $userLogin->browser = @$userAgent['browser'];
        $userLogin->os = @$userAgent['os_platform'];
        $userLogin->save();


        return $user;
    }

    /**
     * Generate unique 4-5 digit numeric referral code
     */
    protected function generateUniqueReferralCode()
    {
        do {
            // Generate random 4-5 digit number
            $code = str_pad(random_int(1000, 99999), 4, '0', STR_PAD_LEFT);
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }

    public function checkUser(Request $request){
        $exist['data'] = false;
        $exist['type'] = null;
        if ($request->mobile) {
            $exist['data'] = User::where('mobile',$request->mobile)->exists();
            $exist['type'] = 'mobile';
        }
        return response($exist);
    }

    public function registered(Request $request, $user)
    {
        return to_route('user.home');
    }

}
