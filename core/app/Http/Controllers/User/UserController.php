<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Lib\GoogleAuthenticator;
use App\Models\CommissionLog;
use App\Models\DailySpotlight;
use App\Models\Faq;
use App\Models\Form;
use App\Models\HoldWalletTransaction;
use App\Models\Plan;
use App\Models\PremiumReferralCommission;
use App\Models\PtcView;
use App\Models\Referral;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\VideoTutorial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function home()
    {
        $pageTitle = 'ড্যাশবোর্ড';
        $user = auth()->user()->load('userNotifications');

        return view($this->activeTemplate . 'user.dashboard_mobile', compact('pageTitle', 'user'));
    }

    public function depositHistory(Request $request)
    {
        $pageTitle = 'Deposit History';
        $deposits = auth()->user()->deposits();
        if ($request->search) {
            $deposits = $deposits->where('trx', $request->search);
        }
        $deposits = $deposits->with(['gateway'])->orderBy('id', 'desc')->paginate(getPaginate());
        return view($this->activeTemplate . 'user.deposit_history', compact('pageTitle', 'deposits'));
    }

    public function show2faForm()
    {
        $general = gs();
        $ga = new GoogleAuthenticator();
        $user = auth()->user();
        $secret = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($user->username . '@' . $general->site_name, $secret);
        $pageTitle = '2FA Setting';
        return view($this->activeTemplate . 'user.twofactor', compact('pageTitle', 'secret', 'qrCodeUrl'));
    }

    public function create2fa(Request $request)
    {
        $user = auth()->user();
        $this->validate($request, [
            'key' => 'required',
            'code' => 'required',
        ]);
        $response = verifyG2fa($user, $request->code, $request->key);
        if ($response) {
            $user->tsc = $request->key;
            $user->ts = 1;
            $user->save();
            $notify[] = ['success', 'Google authenticator activated successfully'];
            return back()->withNotify($notify);
        } else {
            $notify[] = ['error', 'Wrong verification code'];
            return back()->withNotify($notify);
        }
    }

    public function disable2fa(Request $request)
    {
        $this->validate($request, [
            'code' => 'required',
        ]);

        $user = auth()->user();
        $response = verifyG2fa($user, $request->code);
        if ($response) {
            $user->tsc = null;
            $user->ts = 0;
            $user->save();
            $notify[] = ['success', 'Two factor authenticator deactivated successfully'];
        } else {
            $notify[] = ['error', 'Wrong verification code'];
        }
        return back()->withNotify($notify);
    }

    public function transactions(Request $request)
    {
        $pageTitle = 'Transactions';
        $remarks = Transaction::distinct('remark')->orderBy('remark')->get('remark');
        $transactions = Transaction::where('user_id', auth()->id());

        if ($request->search) {
            $transactions = $transactions->where('trx', $request->search);
        }

        if ($request->type) {
            $transactions = $transactions->where('trx_type', $request->type);
        }

        if ($request->remark) {
            $transactions = $transactions->where('remark', $request->remark);
        }

        $transactions = $transactions->orderBy('id', 'desc')->paginate(getPaginate());
        return view($this->activeTemplate . 'user.transactions', compact('pageTitle', 'transactions', 'remarks'));
    }

    public function kycForm()
    {
        if (auth()->user()->kv == 2) {
            $notify[] = ['error', 'Your KYC is under review'];
            return to_route('user.home')->withNotify($notify);
        }
        if (auth()->user()->kv == 1) {
            $notify[] = ['error', 'You are already KYC verified'];
            return to_route('user.home')->withNotify($notify);
        }
        $pageTitle = 'KYC Form';
        $form = Form::where('act', 'kyc')->first();
        return view($this->activeTemplate . 'user.kyc.form', compact('pageTitle', 'form'));
    }

    public function kycData()
    {
        $user = auth()->user();
        $pageTitle = 'KYC Data';
        return view($this->activeTemplate . 'user.kyc.info', compact('pageTitle', 'user'));
    }

    public function kycSubmit(Request $request)
    {
        $form = Form::where('act', 'kyc')->first();
        $formData = $form->form_data;
        $formProcessor = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $userData = $formProcessor->processFormData($request, $formData);
        $user = auth()->user();
        $user->kyc_data = $userData;
        $user->kv = 2;
        $user->save();

        $notify[] = ['success', 'KYC data submitted successfully'];
        return to_route('user.home')->withNotify($notify);
    }

    public function attachmentDownload($fileHash)
    {
        try {
            $filePath = decrypt($fileHash);
        } catch (\Exception $e) {
            abort(404);
        }

        // SECURITY: Validate path is within allowed directories to prevent path traversal
        $allowedPaths = [
            storage_path('app/attachments'),
            storage_path('app/public'),
            public_path('assets/support'),
        ];

        $realPath = realpath($filePath);

        // Check if file exists and path is valid
        if (!$realPath || !file_exists($realPath)) {
            abort(404);
        }

        // Verify the file is within allowed directories
        $isAllowed = false;
        foreach ($allowedPaths as $allowed) {
            $allowedReal = realpath($allowed);
            if ($allowedReal && str_starts_with($realPath, $allowedReal)) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed) {
            abort(403, 'Access denied');
        }

        $extension = pathinfo($realPath, PATHINFO_EXTENSION);
        $general = gs();
        $title = slug($general->site_name) . '-attachments.' . $extension;

        return response()->download($realPath, $title);
    }

    public function userData()
    {
        // User data page is no longer required - redirect to dashboard
        // Registration is now complete in one step
        $user = auth()->user();

        // Ensure reg_step is complete
        if (!$user->reg_step) {
            $user->reg_step = 1;
            $user->save();
        }

        return to_route('user.home');
    }

    public function userDataSubmit(Request $request)
    {
        // User data submission is no longer required - redirect to dashboard
        return to_route('user.home');
    }

    public function buyPlan(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:plans,id'
        ]);

        $plan = Plan::where('status', 1)->findOrFail($request->id);

        // Use database transaction with locking to prevent race conditions
        try {
            $result = DB::transaction(function () use ($plan) {
                // Lock user row for update
                $user = User::lockForUpdate()->find(auth()->id());

                // Validate balance with lock
                if ($plan->price > $user->balance) {
                    throw new \Exception('Oops! You\'ve no sufficient balance');
                }

                // Check for same active plan
                if ($user->runningPlan && $user->plan_id == $plan->id) {
                    throw new \Exception('You couldn\'t subscribe current package till expired');
                }

                // Atomic balance update
                $user->balance -= $plan->price;
                $user->daily_limit = $plan->daily_limit;
                $user->expire_date = now()->addDays($plan->validity);
                $user->plan_id = $plan->id;
                $user->is_premium = $plan->is_premium_package ? 1 : 0;

                // Reset withdrawal counters on new plan purchase
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

                $trx = getTrx();
                $transaction = new Transaction();
                $transaction->user_id = $user->id;
                $transaction->amount = $plan->price;
                $transaction->post_balance = $user->balance;
                $transaction->charge = 0;
                $transaction->trx_type = '-';
                $transaction->details = 'Subscribe ' . $plan->name . ' Plan';
                $transaction->trx = $trx;
                $transaction->remark = 'subscribe_plan';
                $transaction->save();

                $general = gs();
                if ($plan->is_premium_package && $user->ref_by) {
                    $referrer = User::lockForUpdate()->with('plan')->find($user->ref_by);
                    if ($referrer) {
                        $referrerIsPremium = (bool) (
                            $referrer->plan &&
                            $referrer->expire_date &&
                            $referrer->expire_date >= now() &&
                            ($referrer->plan->is_premium_package ?? false)
                        );

                        if (!$referrerIsPremium) {
                            // FREE user refers PREMIUM: Commission = package_number × base_value
                            // e.g., Package 1 = 100 TK, Package 2 = 200 TK, etc.
                            $baseValue = $general->referral_premium_base_value ?? 100;
                            $packageNumber = $plan->package_number ?? $plan->id; // fallback to plan ID
                            $commissionAmount = $packageNumber * $baseValue;

                            PremiumReferralCommission::create([
                                'referrer_id' => $referrer->id,
                                'referred_user_id' => $user->id,
                                'plan_id' => $plan->id,
                                'package_number' => $packageNumber,
                                'amount' => $commissionAmount,
                                'status' => 'pending',
                                'source' => 'plan_subscribe',
                            ]);
                        }
                    }
                }

                return ['user' => $user, 'trx' => $trx];
            }, 3);

            $user = $result['user'];
            $trx = $result['trx'];

        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage()];
            return back()->withNotify($notify);
        }

        // Process commission after transaction commits
        levelCommission($user, $plan->price, 'plan_subscribe_commission', $trx);

        notify($user, 'BUY_PLAN', [
            'plan_name' => $plan->name,
            'amount' => showAmount($plan->price),
            'trx' => $trx,
            'post_balance' => showAmount($user->balance)
        ]);

        // Create in-app notification
        \App\Models\UserNotification::create([
            'user_id' => $user->id,
            'title' => 'প্ল্যান সাবস্ক্রাইব সফল',
            'message' => 'আপনি সফলভাবে "' . $plan->name . '" প্ল্যান সাবস্ক্রাইব করেছেন। মূল্য: ৳' . showAmount($plan->price),
            'type' => 'plan',
        ]);

        $notify[] = ['success', 'You have subscribed to the plan successfully'];
        return back()->withNotify($notify);
    }


    public function commissions(Request $request)
    {
        $pageTitle = "Commissions";
        $commissions = CommissionLog::where('to_id', auth()->id());

        if ($request->search) {
            $search = request()->search;
            $commissions = $commissions->where(function ($q) use ($search) {
                $q->where('trx', 'like', "%$search%")->orWhereHas('userFrom', function ($user) use ($search) {
                    $user->where('username', 'like', "%$search%");
                });
            });
        }

        if ($request->remark) {
            $commissions = $commissions->where('type', $request->remark);
        }
        if ($request->level) {
            $commissions = $commissions->where('level', $request->level);
        }

        $commissions = $commissions->with('userFrom')->paginate(getPaginate());
        $levels = Referral::max('level');
        return view($this->activeTemplate . 'user.commissions', compact('pageTitle', 'commissions', 'levels'));
    }

    public function referredUsers()
    {
        $pageTitle = "Referred Users";
        $user = auth()->user();
        $general = gs();

        // Get referred users with their registration date
        $refUsers = User::where('ref_by', auth()->user()->id)
            ->with('plan')
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate());

        // Get referral commission settings by type (only if enabled)
        $depositCommissions = $general->deposit_commission
            ? Referral::where('commission_type', 'deposit_commission')->orderBy('level')->get()
            : collect();
        $planCommissions = $general->plan_subscribe_commission
            ? Referral::where('commission_type', 'plan_subscribe_commission')->orderBy('level')->get()
            : collect();
        $taskCommissions = $general->ptc_view_commission
            ? Referral::where('commission_type', 'ptc_view_commission')->orderBy('level')->get()
            : collect();

        // Check if any commission type is enabled
        $hasAnyCommission = $general->deposit_commission || $general->plan_subscribe_commission || $general->ptc_view_commission;

        // Calculate user's referral earnings breakdown
        $referralTransactions = Transaction::where('user_id', $user->id)
            ->where(function($q) {
                $q->where('remark', 'like', '%referral%')
                  ->orWhere('remark', 'referral_signup_commission');
            })
            ->where('trx_type', '+')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalReferralEarnings = $referralTransactions->sum('amount');

        // Get signup commission transactions specifically
        $signupCommissions = Transaction::where('user_id', $user->id)
            ->where('remark', 'referral_signup_commission')
            ->where('trx_type', '+')
            ->orderBy('created_at', 'desc')
            ->get();

        // Balance breakdown
        $balanceBreakdown = [
            'main_balance' => $user->balance,
            'referral_hold' => $user->referral_commission_hold ?? 0,
            'upgrade_hold' => $user->upgrade_commission_hold ?? 0,
            'ptc_hold' => $user->ptc_commission_hold ?? 0,
            'total_hold' => ($user->referral_commission_hold ?? 0) + ($user->upgrade_commission_hold ?? 0) + ($user->ptc_commission_hold ?? 0),
            'withdrawable' => $user->balance,
        ];

        // Get PTC earnings
        $ptcEarnings = Transaction::where('user_id', $user->id)
            ->where('remark', 'ptc_view')
            ->where('trx_type', '+')
            ->sum('amount');

        // Check if user is premium (has active subscription plan)
        $isPremium = $user->plan_id && $user->plan_id > 0 && $user->expire_date >= now();

        // Free User Commission Structure (from general settings)
        $freeUserDepositCommission = json_decode($general->free_user_deposit_commission ?? '[]', true) ?: [];
        $freeUserTaskCommission = json_decode($general->free_user_task_commission ?? '[]', true) ?: [];
        $freeUserPlanCommission = json_decode($general->free_user_plan_commission ?? '[]', true) ?: [];

        // Free user settings
        $freeUserSettings = [
            'enabled' => $general->free_user_system_enabled ?? 1,
            'can_earn_referral' => $general->free_user_can_earn_referral ?? 1,
            'referral_level' => $general->free_user_referral_level ?? 1,
            'daily_withdraw_limit' => $general->free_user_daily_withdraw_limit ?? 100,
            'min_withdraw' => $general->free_user_min_withdraw ?? 50,
            'max_withdraw' => $general->free_user_max_withdraw ?? 500,
            'can_view_ptc' => $general->free_user_can_view_ptc ?? 1,
            'ptc_limit' => $general->free_user_ptc_limit ?? 5,
            'ptc_earning' => $general->free_user_ptc_earning ?? 0.5,
            'can_claim_red_bag' => $general->free_user_can_claim_red_bag ?? 0,
            'step_commission_enabled' => $general->free_user_step_commission_enabled ?? 0,
            'step_base_amount' => $general->free_user_step_base_amount ?? 100,
            'step_increment' => $general->free_user_step_increment ?? 100,
            'step_max' => $general->free_user_step_max ?? 10,
        ];

        // Calculate user's current referral count and next step commission
        $userReferralCount = User::where('ref_by', $user->id)->count();
        $nextStepNumber = $userReferralCount + 1;
        $stepCommissionEnabled = $freeUserSettings['step_commission_enabled'];

        // Calculate next commission amount based on step
        $stepBaseAmount = $freeUserSettings['step_base_amount'];
        $stepIncrement = $freeUserSettings['step_increment'];
        $stepMax = $freeUserSettings['step_max'];

        // Calculate: base + (step-1) * increment, capped at max steps
        $effectiveStep = min($nextStepNumber, $stepMax);
        $nextStepCommission = $stepBaseAmount + (($effectiveStep - 1) * $stepIncrement);

        // Signup commission amounts from settings
        $signupReferrerAmount = $general->referral_signup_referrer_amount ?? 10;
        $signupReferredAmount = $general->referral_signup_referred_amount ?? 10;
        $signupCommissionEnabled = $general->referral_signup_commission ?? 1;

        // Check if user got signup bonus (was referred)
        $userGotSignupBonus = Transaction::where('user_id', $user->id)
            ->where('remark', 'referral_signup_bonus')
            ->exists();

        // Get referrer info if exists
        $referrer = $user->ref_by ? User::find($user->ref_by) : null;

        return view($this->activeTemplate . 'user.referred', compact(
            'pageTitle',
            'refUsers',
            'user',
            'depositCommissions',
            'planCommissions',
            'taskCommissions',
            'totalReferralEarnings',
            'hasAnyCommission',
            'referralTransactions',
            'signupCommissions',
            'balanceBreakdown',
            'ptcEarnings',
            'isPremium',
            'freeUserDepositCommission',
            'freeUserTaskCommission',
            'freeUserPlanCommission',
            'freeUserSettings',
            'signupReferrerAmount',
            'signupReferredAmount',
            'signupCommissionEnabled',
            'userGotSignupBonus',
            'referrer',
            'general',
            'userReferralCount',
            'nextStepNumber',
            'nextStepCommission',
            'stepCommissionEnabled'
        ));
    }

    public function premiumCommissions()
    {
        $pageTitle = "প্রিমিয়াম কমিশন";
        $user = auth()->user();
        $general = gs();

        // Get user's premium referral commissions (as referrer)
        $commissions = PremiumReferralCommission::where('referrer_id', $user->id)
            ->with(['referredUser', 'plan', 'admin'])
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate());

        // Stats
        $stats = [
            'total' => PremiumReferralCommission::where('referrer_id', $user->id)->count(),
            'pending' => PremiumReferralCommission::where('referrer_id', $user->id)->where('status', 'pending')->count(),
            'approved' => PremiumReferralCommission::where('referrer_id', $user->id)->where('status', 'approved')->count(),
            'locked' => PremiumReferralCommission::where('referrer_id', $user->id)->where('status', 'locked')->count(),
            'reversed' => PremiumReferralCommission::where('referrer_id', $user->id)->where('status', 'reversed')->count(),
            'total_amount' => PremiumReferralCommission::where('referrer_id', $user->id)->sum('amount'),
            'approved_amount' => PremiumReferralCommission::where('referrer_id', $user->id)->where('status', 'approved')->sum('amount'),
            'pending_amount' => PremiumReferralCommission::where('referrer_id', $user->id)->where('status', 'pending')->sum('amount'),
        ];

        // Check if user is premium
        $isPremium = $user->plan_id && $user->plan && ($user->plan->is_premium_package ?? false) && $user->expire_date >= now();

        // Premium base value from settings
        $premiumBaseValue = $general->referral_premium_base_value ?? 100;

        return view($this->activeTemplate . 'user.premium_commissions', compact(
            'pageTitle',
            'user',
            'commissions',
            'stats',
            'isPremium',
            'premiumBaseValue'
        ));
    }

    public function holdWallet()
    {
        $pageTitle = 'Hold Wallet';
        $user = auth()->user();
        return view($this->activeTemplate . 'user.hold_wallet', compact('pageTitle', 'user'));
    }

    public function holdWalletTransfer()
    {
        // Use database transaction with locking to prevent race conditions
        try {
            $result = DB::transaction(function () {
                // Lock user row for update
                $user = User::lockForUpdate()->find(auth()->id());

                // Lock and fetch available transactions
                $availableTransactions = HoldWalletTransaction::where('user_id', $user->id)
                    ->where('is_transferred', 0)
                    ->where('available_date', '<=', now()->toDateString())
                    ->lockForUpdate()
                    ->get();

                if ($availableTransactions->isEmpty()) {
                    throw new \Exception('No available balance to transfer');
                }

                $totalAmount = $availableTransactions->sum('hold_amount');

                // Batch update with single query
                HoldWalletTransaction::whereIn('id', $availableTransactions->pluck('id'))
                    ->update([
                        'is_transferred' => 1,
                        'transferred_at' => now()
                    ]);

                // Calculate deductions per type using correct column names
                // Commission types stored: 'referral', 'deposit', 'plan_subscribe', 'ptc_view'
                $referralSum = $availableTransactions->where('commission_type', 'referral')->sum('hold_amount');
                $depositSum = $availableTransactions->where('commission_type', 'deposit')->sum('hold_amount');
                $planSubscribeSum = $availableTransactions->where('commission_type', 'plan_subscribe')->sum('hold_amount');
                $ptcViewSum = $availableTransactions->where('commission_type', 'ptc_view')->sum('hold_amount');

                // Update user balances atomically
                $user->balance += $totalAmount;
                $user->referral_commission_hold = max(0, $user->referral_commission_hold - $referralSum);
                $user->upgrade_commission_hold = max(0, $user->upgrade_commission_hold - $depositSum - $planSubscribeSum);
                $user->ptc_commission_hold = max(0, $user->ptc_commission_hold - $ptcViewSum);
                $user->save();

                // Create transaction record
                $trx = getTrx();
                $transaction = new Transaction();
                $transaction->user_id = $user->id;
                $transaction->amount = $totalAmount;
                $transaction->post_balance = $user->balance;
                $transaction->charge = 0;
                $transaction->trx_type = '+';
                $transaction->details = 'Hold wallet transfer to main balance';
                $transaction->remark = 'hold_wallet_transfer';
                $transaction->trx = $trx;
                $transaction->save();

                return ['user' => $user, 'totalAmount' => $totalAmount];
            }, 3);

            $totalAmount = $result['totalAmount'];

        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage()];
            return back()->withNotify($notify);
        }

        // Create notification
        UserNotification::create([
            'user_id' => auth()->id(),
            'title' => 'Hold Wallet Transfer',
            'message' => 'Successfully transferred ৳' . showAmount($totalAmount) . ' from hold wallet to main balance',
            'type' => 'commission',
        ]);

        $notify[] = ['success', 'Successfully transferred ৳' . showAmount($totalAmount) . ' to main balance'];
        return back()->withNotify($notify);
    }

    // Wallet Overview
    public function wallet()
    {
        $pageTitle = 'Wallet';
        $user = auth()->user();
        return view($this->activeTemplate . 'user.wallet', compact('pageTitle', 'user'));
    }

    // Notifications
    public function notifications()
    {
        $pageTitle = 'Notifications';
        $user = auth()->user();
        return view($this->activeTemplate . 'user.notifications', compact('pageTitle', 'user'));
    }

    public function markNotificationsRead()
    {
        auth()->user()->userNotifications()->update(['is_read' => 1]);
        $notify[] = ['success', 'All notifications marked as read'];
        return back()->withNotify($notify);
    }

    // Video Tutorials
    public function videoTutorials()
    {
        $pageTitle = 'Video Tutorials';
        return view($this->activeTemplate . 'user.video_tutorials', compact('pageTitle'));
    }

    public function videoTutorialView($id)
    {
        $tutorial = VideoTutorial::active()->findOrFail($id);
        $pageTitle = $tutorial->title;
        return view($this->activeTemplate . 'user.video_tutorial_view', compact('pageTitle', 'tutorial'));
    }

    // FAQ
    public function faq()
    {
        $pageTitle = 'FAQ';
        return view($this->activeTemplate . 'user.faq', compact('pageTitle'));
    }

    // Daily Spotlights
    public function spotlights()
    {
        $pageTitle = 'Daily Spotlights';
        $spotlights = DailySpotlight::active()->ordered()->paginate(getPaginate());
        return view($this->activeTemplate . 'user.spotlights', compact('pageTitle', 'spotlights'));
    }
}
