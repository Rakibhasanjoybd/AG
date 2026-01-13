<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

/**
 * WalletService handles all balance operations with proper transaction locking.
 * This prevents race conditions that could lead to double-spending or lost updates.
 */
class WalletService
{
    /**
     * Add balance to user account with transaction locking
     *
     * @param int $userId
     * @param float $amount
     * @param string $remark Transaction remark for logging
     * @param string $details Human-readable description
     * @param string|null $trx Transaction reference (auto-generated if null)
     * @param float $charge Any associated charge
     * @return array{user: User, transaction: Transaction}
     * @throws \Exception
     */
    public function addBalance(
        int $userId,
        float $amount,
        string $remark,
        string $details,
        ?string $trx = null,
        float $charge = 0
    ): array {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive');
        }

        return DB::transaction(function () use ($userId, $amount, $remark, $details, $trx, $charge) {
            // Lock user row for update to prevent race conditions
            $user = User::lockForUpdate()->findOrFail($userId);
            
            // Update balance
            $user->balance += $amount;
            $user->save();
            
            // Create transaction record
            $transaction = new Transaction();
            $transaction->user_id = $userId;
            $transaction->amount = $amount;
            $transaction->post_balance = $user->balance;
            $transaction->charge = $charge;
            $transaction->trx_type = '+';
            $transaction->details = $details;
            $transaction->trx = $trx ?? getTrx();
            $transaction->remark = $remark;
            $transaction->save();
            
            return [
                'user' => $user,
                'transaction' => $transaction
            ];
        }, 3); // 3 retry attempts for deadlock handling
    }

    /**
     * Deduct balance from user account with transaction locking
     *
     * @param int $userId
     * @param float $amount
     * @param string $remark
     * @param string $details
     * @param string|null $trx
     * @param float $charge
     * @return array{user: User, transaction: Transaction}
     * @throws \Exception
     */
    public function deductBalance(
        int $userId,
        float $amount,
        string $remark,
        string $details,
        ?string $trx = null,
        float $charge = 0
    ): array {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive');
        }

        return DB::transaction(function () use ($userId, $amount, $remark, $details, $trx, $charge) {
            // Lock user row for update
            $user = User::lockForUpdate()->findOrFail($userId);
            
            // Verify sufficient balance inside transaction
            $totalDeduction = $amount + $charge;
            if ($user->balance < $totalDeduction) {
                throw new \Exception('Insufficient balance');
            }
            
            // Update balance
            $user->balance -= $totalDeduction;
            $user->save();
            
            // Create transaction record
            $transaction = new Transaction();
            $transaction->user_id = $userId;
            $transaction->amount = $amount;
            $transaction->post_balance = $user->balance;
            $transaction->charge = $charge;
            $transaction->trx_type = '-';
            $transaction->details = $details;
            $transaction->trx = $trx ?? getTrx();
            $transaction->remark = $remark;
            $transaction->save();
            
            return [
                'user' => $user,
                'transaction' => $transaction
            ];
        }, 3);
    }

    /**
     * Transfer balance between two users with transaction locking
     *
     * @param int $fromUserId
     * @param int $toUserId
     * @param float $amount
     * @param float $charge Fee charged to sender
     * @return array{from_user: User, to_user: User, trx: string}
     * @throws \Exception
     */
    public function transferBalance(
        int $fromUserId,
        int $toUserId,
        float $amount,
        float $charge = 0
    ): array {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive');
        }

        if ($fromUserId === $toUserId) {
            throw new \InvalidArgumentException('Cannot transfer to same account');
        }

        return DB::transaction(function () use ($fromUserId, $toUserId, $amount, $charge) {
            // Lock both users - order by ID to prevent deadlocks
            $userIds = [$fromUserId, $toUserId];
            sort($userIds);
            
            $users = User::whereIn('id', $userIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            
            $fromUser = $users->get($fromUserId);
            $toUser = $users->get($toUserId);
            
            if (!$fromUser || !$toUser) {
                throw new \Exception('User not found');
            }
            
            $totalDeduction = $amount + $charge;
            if ($fromUser->balance < $totalDeduction) {
                throw new \Exception('Insufficient balance');
            }
            
            $trx = getTrx();
            
            // Deduct from sender
            $fromUser->balance -= $totalDeduction;
            $fromUser->save();
            
            // Add to receiver
            $toUser->balance += $amount;
            $toUser->save();
            
            // Create sender transaction
            $senderTx = new Transaction();
            $senderTx->user_id = $fromUserId;
            $senderTx->amount = $amount;
            $senderTx->post_balance = $fromUser->balance;
            $senderTx->charge = $charge;
            $senderTx->trx_type = '-';
            $senderTx->details = 'Balance transfer to ' . $toUser->username;
            $senderTx->trx = $trx;
            $senderTx->remark = 'balance_transfer';
            $senderTx->save();
            
            // Create receiver transaction
            $receiverTx = new Transaction();
            $receiverTx->user_id = $toUserId;
            $receiverTx->amount = $amount;
            $receiverTx->post_balance = $toUser->balance;
            $receiverTx->charge = 0;
            $receiverTx->trx_type = '+';
            $receiverTx->details = 'Balance received from ' . $fromUser->username;
            $receiverTx->trx = $trx;
            $receiverTx->remark = 'balance_received';
            $receiverTx->save();
            
            return [
                'from_user' => $fromUser,
                'to_user' => $toUser,
                'trx' => $trx
            ];
        }, 3);
    }

    /**
     * Check if user has sufficient balance (for validation before operations)
     *
     * @param int $userId
     * @param float $amount
     * @return bool
     */
    public function hasSufficientBalance(int $userId, float $amount): bool
    {
        return User::where('id', $userId)
            ->where('balance', '>=', $amount)
            ->exists();
    }

    /**
     * Get current balance with fresh database read (bypasses cache)
     *
     * @param int $userId
     * @return float
     */
    public function getCurrentBalance(int $userId): float
    {
        return (float) User::where('id', $userId)->value('balance') ?? 0;
    }

    /**
     * Add to hold balance with transaction locking
     *
     * @param int $userId
     * @param float $amount
     * @param string $holdColumn One of: referral_commission_hold, upgrade_commission_hold, ptc_commission_hold
     * @return User
     * @throws \Exception
     */
    public function addToHoldBalance(int $userId, float $amount, string $holdColumn): User
    {
        $validColumns = ['referral_commission_hold', 'upgrade_commission_hold', 'ptc_commission_hold'];
        
        if (!in_array($holdColumn, $validColumns)) {
            throw new \InvalidArgumentException("Invalid hold column: {$holdColumn}");
        }

        return DB::transaction(function () use ($userId, $amount, $holdColumn) {
            $user = User::lockForUpdate()->findOrFail($userId);
            $user->$holdColumn += $amount;
            $user->save();
            
            return $user;
        });
    }

    /**
     * Transfer from hold balance to main balance
     *
     * @param int $userId
     * @param float $amount
     * @param string $holdColumn
     * @return array{user: User, transaction: Transaction}
     * @throws \Exception
     */
    public function transferFromHoldBalance(int $userId, float $amount, string $holdColumn): array
    {
        $validColumns = ['referral_commission_hold', 'upgrade_commission_hold', 'ptc_commission_hold'];
        
        if (!in_array($holdColumn, $validColumns)) {
            throw new \InvalidArgumentException("Invalid hold column: {$holdColumn}");
        }

        return DB::transaction(function () use ($userId, $amount, $holdColumn) {
            $user = User::lockForUpdate()->findOrFail($userId);
            
            if ($user->$holdColumn < $amount) {
                throw new \Exception('Insufficient hold balance');
            }
            
            $user->$holdColumn -= $amount;
            $user->balance += $amount;
            $user->save();
            
            $trx = getTrx();
            
            $transaction = new Transaction();
            $transaction->user_id = $userId;
            $transaction->amount = $amount;
            $transaction->post_balance = $user->balance;
            $transaction->charge = 0;
            $transaction->trx_type = '+';
            $transaction->details = 'Hold wallet transfer to main balance';
            $transaction->trx = $trx;
            $transaction->remark = 'hold_wallet_transfer';
            $transaction->save();
            
            return [
                'user' => $user,
                'transaction' => $transaction
            ];
        }, 3);
    }
}
