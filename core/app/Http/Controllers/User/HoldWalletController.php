<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\HoldWalletSetting;
use App\Models\HoldWalletTransaction;
use App\Models\HoldWalletTransfer;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HoldWalletController extends Controller
{
    /**
     * Display Hold Wallet page with achievement progress
     */
    public function index()
    {
        $pageTitle = 'হোল্ড ওয়ালেট';
        $user = auth()->user();

        // Update achievement stats
        $user->updateAchievementStats();

        // Get achievement data
        $achievementProgress = $user->getAchievementProgress();
        $transferStatus = $user->canTransferFromHoldWallet();
        $achievementConfig = HoldWalletSetting::getAchievementConfig();
        $feeConfig = HoldWalletSetting::getTransferFeeConfig();

        // Get available balance based on level
        $availableBalance = $user->getAvailableHoldBalanceByLevel();
        $pendingBalance = $user->pendingHoldBalance();
        $totalHoldBalance = $user->totalHoldBalance;

        // Calculate estimated fee
        $estimatedFee = HoldWalletSetting::calculateTransferFee($availableBalance);
        $netAmount = max(0, $availableBalance - $estimatedFee);

        // Get recent transactions
        $holdTransactions = $user->holdWalletTransactions()
            ->latest()
            ->paginate(15);

        // Get transfer history
        $transferHistory = $user->holdWalletTransfers()
            ->latest()
            ->take(10)
            ->get();

        return view($this->activeTemplate . 'user.hold_wallet', compact(
            'pageTitle',
            'user',
            'achievementProgress',
            'transferStatus',
            'achievementConfig',
            'feeConfig',
            'availableBalance',
            'pendingBalance',
            'totalHoldBalance',
            'estimatedFee',
            'netAmount',
            'holdTransactions',
            'transferHistory'
        ));
    }

    /**
     * Transfer from Hold Wallet to Main Wallet with level-based rules
     */
    public function transfer(Request $request)
    {
        try {
            $result = DB::transaction(function () {
                // Lock user row for update
                $user = User::lockForUpdate()->find(auth()->id());

                // Check transfer eligibility based on achievement level
                $transferStatus = $user->canTransferFromHoldWallet();

                if (!$transferStatus['can_transfer']) {
                    throw new \Exception($transferStatus['reason']);
                }

                $level = $user->getAchievementLevel();
                $feeConfig = HoldWalletSetting::getTransferFeeConfig();

                // Get available transactions based on level
                if ($level >= 3) {
                    // Gold level: All hold balance is available
                    $availableTransactions = HoldWalletTransaction::where('user_id', $user->id)
                        ->where('is_transferred', 0)
                        ->lockForUpdate()
                        ->get();
                } else {
                    // Other levels: Only transactions past available_date
                    $availableTransactions = HoldWalletTransaction::where('user_id', $user->id)
                        ->where('is_transferred', 0)
                        ->where('available_date', '<=', now()->toDateString())
                        ->lockForUpdate()
                        ->get();
                }

                if ($availableTransactions->isEmpty()) {
                    throw new \Exception('ট্রান্সফারের জন্য কোনো ব্যালেন্স নেই');
                }

                $totalAmount = $availableTransactions->sum('hold_amount');

                // Check minimum transfer amount
                if ($totalAmount < $feeConfig['min_transfer']) {
                    throw new \Exception('সর্বনিম্ন ট্রান্সফার পরিমাণ ৳' . showAmount($feeConfig['min_transfer']));
                }

                // Calculate fee
                $fee = HoldWalletSetting::calculateTransferFee($totalAmount);
                $netAmount = $totalAmount - $fee;

                if ($netAmount <= 0) {
                    throw new \Exception('ফি কাটার পর ব্যালেন্স শূন্য হয়ে যাচ্ছে');
                }

                // Mark transactions as transferred
                HoldWalletTransaction::whereIn('id', $availableTransactions->pluck('id'))
                    ->update([
                        'is_transferred' => 1,
                        'transferred_at' => now(),
                        'transfer_fee' => $fee / $availableTransactions->count(), // Distribute fee
                    ]);

                // Calculate deductions per type
                $referralSum = $availableTransactions->where('commission_type', 'referral')->sum('hold_amount');
                $depositSum = $availableTransactions->where('commission_type', 'deposit')->sum('hold_amount');
                $planSubscribeSum = $availableTransactions->where('commission_type', 'plan_subscribe')->sum('hold_amount');
                $ptcViewSum = $availableTransactions->where('commission_type', 'ptc_view')->sum('hold_amount');

                // Update user balances atomically
                $user->balance += $netAmount;
                $user->referral_commission_hold = max(0, $user->referral_commission_hold - $referralSum);
                $user->upgrade_commission_hold = max(0, $user->upgrade_commission_hold - $depositSum - $planSubscribeSum);
                $user->ptc_commission_hold = max(0, $user->ptc_commission_hold - $ptcViewSum);
                $user->last_transfer_date = now()->toDateString();
                $user->save();

                $trx = getTrx();

                // Create transaction record
                $transaction = new Transaction();
                $transaction->user_id = $user->id;
                $transaction->amount = $netAmount;
                $transaction->post_balance = $user->balance;
                $transaction->charge = $fee;
                $transaction->trx_type = '+';
                $transaction->details = 'Hold wallet transfer to main balance (Fee: ৳' . showAmount($fee) . ')';
                $transaction->remark = 'hold_wallet_transfer';
                $transaction->trx = $trx;
                $transaction->save();

                // Create transfer log
                HoldWalletTransfer::create([
                    'user_id' => $user->id,
                    'amount' => $totalAmount,
                    'fee' => $fee,
                    'net_amount' => $netAmount,
                    'achievement_level' => $level,
                    'referral_count_at_transfer' => $user->total_referrals_count,
                    'trx' => $trx,
                    'details' => 'Transferred from hold wallet at Level ' . $level,
                ]);

                return [
                    'user' => $user,
                    'totalAmount' => $totalAmount,
                    'fee' => $fee,
                    'netAmount' => $netAmount,
                    'level' => $level,
                ];
            }, 3);

            // Create notification
            UserNotification::create([
                'user_id' => auth()->id(),
                'title' => 'হোল্ড ওয়ালেট ট্রান্সফার সফল',
                'message' => 'সফলভাবে ৳' . showAmount($result['netAmount']) . ' মূল ব্যালেন্সে ট্রান্সফার হয়েছে (ফি: ৳' . showAmount($result['fee']) . ')',
                'type' => 'commission',
            ]);

            $notify[] = ['success', 'সফলভাবে ৳' . showAmount($result['netAmount']) . ' মূল ব্যালেন্সে ট্রান্সফার হয়েছে'];
            return back()->withNotify($notify);

        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage()];
            return back()->withNotify($notify);
        }
    }

    /**
     * Get achievement info API (for AJAX)
     */
    public function getAchievementInfo()
    {
        $user = auth()->user();
        $user->updateAchievementStats();

        return response()->json([
            'success' => true,
            'data' => [
                'achievement_progress' => $user->getAchievementProgress(),
                'transfer_status' => $user->canTransferFromHoldWallet(),
                'available_balance' => $user->getAvailableHoldBalanceByLevel(),
                'pending_balance' => $user->pendingHoldBalance(),
                'total_hold_balance' => $user->totalHoldBalance,
                'fee_config' => HoldWalletSetting::getTransferFeeConfig(),
            ],
        ]);
    }

    /**
     * Get transfer history API (for AJAX)
     */
    public function getTransferHistory()
    {
        $user = auth()->user();
        $transfers = $user->holdWalletTransfers()
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $transfers,
        ]);
    }
}
