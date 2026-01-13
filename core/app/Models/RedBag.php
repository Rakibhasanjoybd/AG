<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedBag extends Model
{
    protected $guarded = [];

    protected $casts = [
        'status' => 'boolean',
        'require_referral' => 'boolean',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'win_probability' => 'decimal:2',
        'total_daily_budget' => 'decimal:2',
        'spent_today' => 'decimal:2',
    ];

    public function claims()
    {
        return $this->hasMany(RedBagClaim::class);
    }

    public function isAvailable()
    {
        if (!$this->status) {
            return false;
        }

        $now = now();
        $currentTime = $now->format('H:i:s');

        // Check time window
        if ($currentTime < $this->start_time || $currentTime > $this->end_time) {
            return false;
        }

        // Check daily budget
        if ($this->budget_reset_date != $now->toDateString()) {
            $this->update([
                'spent_today' => 0,
                'budget_reset_date' => $now->toDateString()
            ]);
        }

        if ($this->spent_today >= $this->total_daily_budget) {
            return false;
        }

        return true;
    }

    public function getRandomAmount()
    {
        $remaining = $this->total_daily_budget - $this->spent_today;
        $maxPossible = min($this->max_amount, $remaining);

        if ($maxPossible < $this->min_amount) {
            return $this->min_amount;
        }

        return round(mt_rand($this->min_amount * 100, $maxPossible * 100) / 100, 2);
    }

    public function shouldWin()
    {
        return mt_rand(1, 10000) <= ($this->win_probability * 100);
    }

    public function getUserDailyClaimCount($userId)
    {
        return $this->claims()
            ->where('user_id', $userId)
            ->whereDate('created_at', today())
            ->count();
    }

    public function canUserClaim($user)
    {
        $dailyClaims = $this->getUserDailyClaimCount($user->id);
        $limit = $this->daily_limit;

        // New user bonus
        if ($user->created_at->diffInDays(now()) <= $this->new_user_days) {
            $limit += $this->new_user_bonus_count;
        }

        return $dailyClaims < $limit;
    }

    public function getUserRemainingClaims($user)
    {
        $dailyClaims = $this->getUserDailyClaimCount($user->id);
        $limit = $this->daily_limit;

        if ($user->created_at->diffInDays(now()) <= $this->new_user_days) {
            $limit += $this->new_user_bonus_count;
        }

        return max(0, $limit - $dailyClaims);
    }
}
