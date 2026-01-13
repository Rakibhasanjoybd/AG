<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Models\AdminNotification;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use App\Models\WithdrawMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class WithdrawController extends Controller
{

    public function withdrawMoney()
    {
        $user = auth()->user();
        $withdrawMethods = WithdrawMethod::where('status',1)->get();
        $pageTitle = 'Withdraw Money';

        // Get withdrawal status
        $withdrawStatus = $user->canWithdrawNow();

        // Non-premium lifetime withdrawal limit info
        $general = gs();
        $isPremiumPackage = (bool) ($user->plan && ($user->plan->is_premium_package ?? false));
        $nonPremiumLimitInfo = null;
        $freeUserMaxWithdraw = $general->free_user_max_withdraw ?? 1000;
        if (!$isPremiumPackage) {
            $limit = $general->non_premium_withdraw_limit ?? 1000;
            $used = $user->non_premium_withdraw_used ?? 0;
            $remaining = max(0, $limit - $used);
            $nonPremiumLimitInfo = [
                'limit' => $limit,
                'used' => $used,
                'remaining' => $remaining,
                'percent_used' => $limit > 0 ? round(($used / $limit) * 100, 1) : 0,
                'per_withdrawal_max' => $freeUserMaxWithdraw
            ];
        }

        return view($this->activeTemplate.'user.withdraw.methods', compact('pageTitle','withdrawMethods', 'withdrawStatus', 'isPremiumPackage', 'nonPremiumLimitInfo', 'freeUserMaxWithdraw'));
    }

    public function withdrawStore(Request $request)
    {
        // Validate all inputs including PIN and wallet number
        $this->validate($request, [
            'method_id' => 'required|exists:withdraw_methods,id',
            'wallet_number' => 'required|string|min:11|max:20',
            'amount' => 'required|numeric|min:1',
            'withdrawal_pin' => 'required|digits_between:4,6'
        ], [
            'method_id.required' => 'উত্তোলন পদ্ধতি নির্বাচন করুন',
            'wallet_number.required' => 'ওয়ালেট নম্বর দিন',
            'wallet_number.min' => 'সঠিক ওয়ালেট নম্বর দিন',
            'withdrawal_pin.required' => 'উত্তোলন পিন দিন',
            'withdrawal_pin.digits_between' => 'পিন ৪-৬ ডিজিট হতে হবে'
        ]);

        $user = auth()->user();

        // Verify withdrawal PIN first
        if (!$user->verifyWithdrawalPin($request->withdrawal_pin)) {
            $notify[] = ['error', 'ভুল উত্তোলন পিন'];
            return back()->withNotify($notify)->withInput();
        }

        // Get the withdraw method
        $method = WithdrawMethod::where('id', $request->method_id)->where('status', 1)->first();

        if (!$method) {
            $notify[] = ['error', 'এই উত্তোলন পদ্ধতি বর্তমানে বন্ধ আছে'];
            return back()->withNotify($notify);
        }

        // Check withdrawal permission
        $withdrawStatus = $user->canWithdrawNow();
        if (!$withdrawStatus['can_withdraw']) {
            $notify[] = ['error', $withdrawStatus['reason']];
            return back()->withNotify($notify);
        }

        // Enforce non-premium lifetime withdrawal limit (amount based)
        $general = gs();
        $isPremiumPackage = (bool) ($user->plan && ($user->plan->is_premium_package ?? false));
        if (!$isPremiumPackage) {
            $limit = $general->non_premium_withdraw_limit ?? 1000;
            $used = $user->non_premium_withdraw_used ?? 0;
            $remaining = $limit - $used;

            if ($request->amount > $remaining) {
                $notify[] = ['error', 'আপনার নন-প্রিমিয়াম লাইফটাইম উত্তোলন লিমিটে পর্যাপ্ত পরিমাণ বাকি নেই।'];
                return back()->withNotify($notify);
            }
        }

        if ($request->amount < $method->min_limit) {
            $notify[] = ['error', 'সর্বনিম্ন পরিমাণ ৳' . showAmount($method->min_limit)];
            return back()->withNotify($notify);
        }
        if ($request->amount > $method->max_limit) {
            $notify[] = ['error', 'সর্বোচ্চ পরিমাণ ৳' . showAmount($method->max_limit)];
            return back()->withNotify($notify);
        }

        // Enforce per-withdrawal limits for FREE users
        if (!$isPremiumPackage) {
            // Min withdrawal limit for free users
            $freeUserMinWithdraw = $general->free_user_min_withdraw ?? 50;
            if ($request->amount < $freeUserMinWithdraw) {
                $notify[] = ['error', 'ফ্রি মেম্বারদের জন্য সর্বনিম্ন উত্তোলন ৳' . showAmount($freeUserMinWithdraw)];
                return back()->withNotify($notify);
            }

            // Max withdrawal limit for free users (1000 TK default)
            $freeUserMaxWithdraw = $general->free_user_max_withdraw ?? 1000;
            if ($request->amount > $freeUserMaxWithdraw) {
                $notify[] = ['error', 'ফ্রি মেম্বারদের জন্য প্রতি উত্তোলনে সর্বোচ্চ ৳' . showAmount($freeUserMaxWithdraw) . ' অনুমোদিত'];
                return back()->withNotify($notify);
            }

            // Daily withdrawal limit check for free users
            $freeUserDailyLimit = $general->free_user_daily_withdraw_limit ?? 100;
            $todayWithdrawals = Withdrawal::where('user_id', $user->id)
                ->whereDate('created_at', now()->toDateString())
                ->whereIn('status', [1, 2]) // pending or approved
                ->sum('amount');

            if (($todayWithdrawals + $request->amount) > $freeUserDailyLimit) {
                $remaining = max(0, $freeUserDailyLimit - $todayWithdrawals);
                $notify[] = ['error', 'ফ্রি মেম্বারদের দৈনিক উত্তোলন লিমিট ৳' . showAmount($freeUserDailyLimit) . '। আজ বাকি আছে ৳' . showAmount($remaining)];
                return back()->withNotify($notify);
            }
        }

        if ($request->amount > $user->balance) {
            $notify[] = ['error', 'পর্যাপ্ত ব্যালেন্স নেই'];
            return back()->withNotify($notify);
        }

        // 2FA verification if enabled
        if ($user->ts) {
            $response = verifyG2fa($user, $request->authenticator_code);
            if (!$response) {
                $notify[] = ['error', 'Wrong verification code'];
                return back()->withNotify($notify);
            }
        }

        $charge = $method->fixed_charge + ($request->amount * $method->percent_charge / 100);
        $afterCharge = $request->amount - $charge;
        $finalAmount = $afterCharge * $method->rate;

        // Use database transaction with locking to prevent race conditions
        $walletNumber = $request->wallet_number;
        try {
            $result = DB::transaction(function () use ($request, $method, $walletNumber, $charge, $afterCharge, $finalAmount, $isPremiumPackage) {
                // Lock user row for update to prevent concurrent modifications
                $user = User::lockForUpdate()->with('plan')->find(auth()->id());

                // Re-check withdrawal permission with locked user state
                $freshWithdrawStatus = $user->canWithdrawNow();
                if (!$freshWithdrawStatus['can_withdraw']) {
                    throw new \Exception($freshWithdrawStatus['reason']);
                }

                // Re-validate balance inside transaction with lock
                if ($request->amount > $user->balance) {
                    throw new \Exception('পর্যাপ্ত ব্যালেন্স নেই');
                }

                // Create withdrawal record
                $withdraw = new Withdrawal();
                $withdraw->method_id = $method->id;
                $withdraw->user_id = $user->id;
                $withdraw->amount = $request->amount;
                $withdraw->currency = $method->currency;
                $withdraw->rate = $method->rate;
                $withdraw->charge = $charge;
                $withdraw->final_amount = $finalAmount;
                $withdraw->after_charge = $afterCharge;
                $withdraw->trx = getTrx();
                $withdraw->status = 2; // Pending approval

                // Store wallet info
                $withdrawInformation = [
                    'wallet_number' => $walletNumber,
                    'method_name' => $method->name
                ];

                // Enforce + reserve non-premium withdrawal limits
                $general = gs();
                $isPremiumPackage = (bool) ($user->plan && ($user->plan->is_premium_package ?? false));
                if (!$isPremiumPackage) {
                    // Per-withdrawal max limit for FREE users (1000 TK default)
                    $freeUserMaxWithdraw = $general->free_user_max_withdraw ?? 1000;
                    if ($withdraw->amount > $freeUserMaxWithdraw) {
                        throw new \Exception('ফ্রি মেম্বারদের জন্য প্রতি উত্তোলনে সর্বোচ্চ ৳' . showAmount($freeUserMaxWithdraw) . ' অনুমোদিত');
                    }

                    // Lifetime withdrawal limit
                    $limit = $general->non_premium_withdraw_limit ?? 1000;
                    $used = $user->non_premium_withdraw_used ?? 0;
                    $remaining = $limit - $used;
                    if ($withdraw->amount > $remaining) {
                        throw new \Exception('আপনার নন-প্রিমিয়াম লাইফটাইম উত্তোলন লিমিটে পর্যাপ্ত পরিমাণ বাকি নেই।');
                    }

                    $user->non_premium_withdraw_used = $used + $withdraw->amount;
                    $withdrawInformation['non_premium_limit_applied'] = 1;
                }

                $withdraw->withdraw_information = $withdrawInformation;
                $withdraw->save();

                // Atomic balance deduction
                $user->balance -= $withdraw->amount;

                // Deduct withdrawal limit based on type
                if ($freshWithdrawStatus['type'] === 'anytime') {
                    if (User::usersTableHasColumn('anytime_withdraw_used')) {
                        $user->anytime_withdraw_used = ($user->anytime_withdraw_used ?? 0) + 1;
                    }
                } elseif ($freshWithdrawStatus['type'] === 'weekly') {
                    if (User::usersTableHasColumn('last_weekly_withdraw')) {
                        $user->last_weekly_withdraw = now()->toDateString();
                    }
                }

                $user->save();

                // Create transaction record
                $transaction = new Transaction();
                $transaction->user_id = $withdraw->user_id;
                $transaction->amount = $withdraw->amount;
                $transaction->post_balance = $user->balance;
                $transaction->charge = $withdraw->charge;
                $transaction->trx_type = '-';
                $transaction->details = showAmount($withdraw->final_amount) . ' ' . $withdraw->currency . ' Withdraw Via ' . $method->name;
                $transaction->trx = $withdraw->trx;
                $transaction->remark = 'withdraw';
                $transaction->save();

                return ['user' => $user, 'withdraw' => $withdraw];
            }, 3); // 3 retry attempts for deadlocks

            $user = $result['user'];
            $withdraw = $result['withdraw'];

        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage()];
            return back()->withNotify($notify);
        }

        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->title = 'New withdraw request from '.$user->username;
        $adminNotification->click_url = urlPath('admin.withdraw.details',$withdraw->id);
        $adminNotification->save();

        // HIGH VALUE WITHDRAWAL ALERT: Email admin immediately for large withdrawals
        $general = gs();
        $highValueThreshold = $general->high_value_withdraw_threshold ?? 10000;
        if ($withdraw->amount >= $highValueThreshold) {
            // Create urgent admin notification
            $urgentNotification = new AdminNotification();
            $urgentNotification->user_id = $user->id;
            $urgentNotification->title = '⚠️ HIGH VALUE WITHDRAW: ৳' . showAmount($withdraw->amount) . ' from ' . $user->username;
            $urgentNotification->click_url = urlPath('admin.withdraw.details', $withdraw->id);
            $urgentNotification->save();
            
            // Send email alert to admin
            notify($general->email_from, 'ADMIN_HIGH_WITHDRAW_ALERT', [
                'username' => $user->username,
                'amount' => showAmount($withdraw->amount),
                'method' => $withdraw->method->name ?? 'Unknown',
                'wallet' => $walletNumber,
                'trx' => $withdraw->trx,
                'user_balance' => showAmount($user->balance),
                'time' => now()->format('Y-m-d H:i:s')
            ]);
        }

        notify($user, 'WITHDRAW_REQUEST', [
            'method_name' => $withdraw->method->name,
            'method_currency' => $withdraw->currency,
            'method_amount' => showAmount($withdraw->final_amount),
            'amount' => showAmount($withdraw->amount),
            'charge' => showAmount($withdraw->charge),
            'rate' => showAmount($withdraw->rate),
            'trx' => $withdraw->trx,
            'post_balance' => showAmount($user->balance),
        ]);

        // Create in-app notification
        \App\Models\UserNotification::create([
            'user_id' => $user->id,
            'title' => 'উত্তোলন অনুরোধ জমা দেওয়া হয়েছে',
            'message' => 'আপনার ৳' . showAmount($withdraw->amount) . ' উত্তোলন অনুরোধ সফলভাবে জমা দেওয়া হয়েছে। অনুমোদনের জন্য অপেক্ষা করুন।',
            'type' => 'withdrawal',
        ]);

        $notify[] = ['success', 'উত্তোলন অনুরোধ সফলভাবে জমা দেওয়া হয়েছে'];
        return to_route('user.withdraw.history')->withNotify($notify);
    }

    public function withdrawLog(Request $request)
    {
        $pageTitle = "Withdraw Log";
        $withdraws = Withdrawal::where('user_id', auth()->id())->where('status', '!=', 0);
        if ($request->search) {
            $withdraws = $withdraws->where('trx',$request->search);
        }
        $withdraws = $withdraws->with('method')->orderBy('id','desc')->paginate(getPaginate());
        return view($this->activeTemplate.'user.withdraw.log', compact('pageTitle','withdraws'));
    }
}
