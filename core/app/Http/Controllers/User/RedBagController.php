<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RedBag;
use App\Models\RedBagClaim;
use App\Models\RedBagDevice;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RedBagController extends Controller
{
    public function checkAvailability()
    {
        $user = auth()->user();
        $redBag = RedBag::where('status', true)->first();

        if (!$redBag) {
            return response()->json([
                'available' => false,
                'message' => 'No active red bag'
            ]);
        }

        if (!$redBag->isAvailable()) {
            return response()->json([
                'available' => false,
                'message' => 'Red bag not available at this time'
            ]);
        }

        if (!$redBag->canUserClaim($user)) {
            return response()->json([
                'available' => false,
                'message' => 'Daily limit reached',
                'remaining' => 0
            ]);
        }

        // Check referral requirement
        if ($redBag->require_referral && $redBag->min_referrals > 0) {
            $referralCount = \App\Models\User::where('ref_by', $user->id)->count();
            if ($referralCount < $redBag->min_referrals) {
                return response()->json([
                    'available' => false,
                    'message' => "Need {$redBag->min_referrals} referrals to claim",
                    'referral_required' => true,
                    'current_referrals' => $referralCount,
                    'required_referrals' => $redBag->min_referrals
                ]);
            }
        }

        return response()->json([
            'available' => true,
            'remaining' => $redBag->getUserRemainingClaims($user),
            'red_bag_id' => $redBag->id,
            'start_time' => $redBag->start_time,
            'end_time' => $redBag->end_time
        ]);
    }

    public function claim(Request $request)
    {
        $user = auth()->user();
        $deviceId = $request->input('device_id');
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $sessionHash = md5(session()->getId() . $userAgent);

        $redBag = RedBag::where('status', true)->first();

        if (!$redBag) {
            return response()->json([
                'success' => false,
                'message' => 'কোনো সক্রিয় রেড ব্যাগ নেই'
            ], 400);
        }

        if (!$redBag->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'রেড ব্যাগ এখন উপলব্ধ নয়'
            ], 400);
        }

        if (!$redBag->canUserClaim($user)) {
            return response()->json([
                'success' => false,
                'message' => 'আজকের জন্য আপনার রেড ব্যাগ শেষ হয়ে গেছে'
            ], 400);
        }

        // Check if device is blocked
        if (RedBagDevice::isBlocked($deviceId)) {
            return response()->json([
                'success' => false,
                'message' => 'এই ডিভাইস থেকে রেড ব্যাগ সংগ্রহ করা যাবে না'
            ], 403);
        }

        // Perform fraud check
        $fraudCheck = RedBagClaim::performFraudCheck($user->id, $deviceId, $ipAddress);

        return DB::transaction(function () use ($user, $redBag, $deviceId, $ipAddress, $userAgent, $sessionHash, $fraudCheck) {

            // Lock the red bag row for update
            $redBag = RedBag::lockForUpdate()->find($redBag->id);

            // Lock user for update to prevent race conditions on balance
            $user = \App\Models\User::lockForUpdate()->find($user->id);

            // Determine if user wins
            $isWinner = !$fraudCheck['is_fraud'] && $redBag->shouldWin();
            $amount = 0;

            if ($isWinner) {
                $amount = $redBag->getRandomAmount();

                // Update user balance (now safely locked)
                $user->balance += $amount;
                $user->save();

                // Update red bag spent amount
                $redBag->spent_today += $amount;
                $redBag->save();

                // Create transaction record
                Transaction::create([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'post_balance' => $user->balance,
                    'charge' => 0,
                    'trx_type' => '+',
                    'details' => 'Red Bag Bonus',
                    'trx' => getTrx(),
                    'remark' => 'red_bag_bonus'
                ]);
            }

            // Create claim record
            $claim = RedBagClaim::create([
                'user_id' => $user->id,
                'red_bag_id' => $redBag->id,
                'amount' => $amount,
                'is_winner' => $isWinner,
                'device_id' => $deviceId,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'session_hash' => $sessionHash,
                'is_fraudulent' => $fraudCheck['is_fraud'],
                'fraud_reason' => $fraudCheck['reason']
            ]);

            // Record device
            RedBagDevice::recordDevice($deviceId, $user->id);

            // Prepare message
            if ($fraudCheck['is_fraud']) {
                $message = 'তুমি আগামীকাল ভালো কিছু পাবে, তোমার জন্য শুভকামনা।';
                $isWinner = false;
            } elseif ($isWinner) {
                $message = str_replace('{amount}', showAmount($amount), $redBag->winning_message);
            } else {
                $message = $redBag->losing_message;
            }

            return response()->json([
                'success' => true,
                'is_winner' => $isWinner,
                'amount' => $amount,
                'formatted_amount' => showAmount($amount),
                'message' => $message,
                'remaining_claims' => $redBag->getUserRemainingClaims($user),
                'new_balance' => showAmount($user->balance)
            ]);
        });
    }

    public function history()
    {
        $user = auth()->user();
        $claims = RedBagClaim::where('user_id', $user->id)
            ->where('is_fraudulent', false)
            ->latest()
            ->paginate(getPaginate());

        $pageTitle = 'Red Bag History';
        return view('templates.basic.user.red_bag.history', compact('pageTitle', 'claims'));
    }

    public function getStats()
    {
        $user = auth()->user();

        $stats = [
            'total_claims' => RedBagClaim::where('user_id', $user->id)->where('is_fraudulent', false)->count(),
            'total_wins' => RedBagClaim::where('user_id', $user->id)->where('is_winner', true)->where('is_fraudulent', false)->count(),
            'total_earned' => RedBagClaim::where('user_id', $user->id)->where('is_winner', true)->where('is_fraudulent', false)->sum('amount'),
            'today_claims' => RedBagClaim::where('user_id', $user->id)->whereDate('created_at', today())->where('is_fraudulent', false)->count(),
        ];

        $redBag = RedBag::where('status', true)->first();
        $stats['remaining_today'] = $redBag ? $redBag->getUserRemainingClaims($user) : 0;

        return response()->json($stats);
    }
}
