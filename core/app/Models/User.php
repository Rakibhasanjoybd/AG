<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\HoldWalletSetting;
use App\Models\HoldWalletTransfer;

class User extends Authenticatable
{

    /**
     * Cache for schema column existence checks.
     * Prevents repeated INFORMATION_SCHEMA lookups in hot paths.
     */
    protected static array $schemaColumnExistsCache = [];

    public static function usersTableHasColumn(string $column): bool
    {
        $key = 'users.' . $column;
        if (!array_key_exists($key, self::$schemaColumnExistsCache)) {
            try {
                self::$schemaColumnExistsCache[$key] = Schema::hasColumn('users', $column);
            } catch (\Throwable $e) {
                // If schema lookup fails (misconfigured DB), be safe and assume it doesn't exist.
                self::$schemaColumnExistsCache[$key] = false;
            }
        }

        return (bool) self::$schemaColumnExistsCache[$key];
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'withdrawal_pin',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'address' => 'object',
        'kyc_data' => 'object',
        'ver_code_send_at' => 'datetime',
        'hold_balance' => 'decimal:8',
        'referral_commission_hold' => 'decimal:8',
        'upgrade_commission_hold' => 'decimal:8',
        'ptc_commission_hold' => 'decimal:8',
        'anytime_withdraw_used' => 'integer',
        'non_premium_withdraw_used' => 'decimal:8',
        'ptc_unlock_level' => 'integer',
        'ptc_income_locked' => 'boolean',
        'last_weekly_withdraw' => 'date',
        'plan_purchase_date' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fullname', 'firstname', 'lastname', 'email', 'mobile', 'password',
        'withdrawal_pin', 'referral_code', 'username', 'ref_by', 'country_code',
        'address', 'status', 'kv', 'ev', 'sv', 'ts', 'tv'
    ];

    /**
     * Verify withdrawal PIN
     */
    public function verifyWithdrawalPin($pin)
    {
        return Hash::check($pin, $this->withdrawal_pin);
    }

    /**
     * Get user's referral link using unique referral code
     */
    public function referralLink(): Attribute
    {
        return new Attribute(
            get: fn () => route('user.register') . '?ref=' . $this->referral_code,
        );
    }

    /**
     * Get formatted user ID (ID - XXXXXX) for display
     */
    public function userId(): Attribute
    {
        return new Attribute(
            get: fn () => 'ID - ' . str_pad($this->id, 6, '0', STR_PAD_LEFT),
        );
    }

    public function loginLogs()
    {
        return $this->hasMany(UserLogin::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->orderBy('id','desc');
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class)->where('status','!=',0);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class)->where('status','!=',0);
    }

    public function premiumReferralCommissions()
    {
        return $this->hasMany(PremiumReferralCommission::class, 'referrer_id');
    }

    public function receivedPremiumReferralCommissions()
    {
        return $this->hasMany(PremiumReferralCommission::class, 'referred_user_id');
    }

    public function fullname(): Attribute
    {
        return new Attribute(
            get: fn () => $this->firstname . ' ' . $this->lastname,
        );
    }

    public function runningPlan(): Attribute
    {
        if ($this->plan && $this->expire_date > now()) {
            $running = true;
        }else{
            $running = false;
        }
        return new Attribute(
            get: fn () => $running,
        );
    }


    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function clicks()
    {
        return $this->hasMany(PtcView::class);
    }

    public function commissions()
    {
        return $this->hasMany(CommissionLog::class);
    }


    public function refBy()
    {
        return $this->belongsTo(User::class,'ref_by');
    }

    // SCOPES
    public function scopeActive()
    {
        return $this->where('status', 1);
    }

    public function scopeBanned()
    {
        return $this->where('status', 0);
    }

    public function scopeEmailUnverified()
    {
        return $this->where('ev', 0);
    }

    public function scopeMobileUnverified()
    {
        return $this->where('sv', 0);
    }

    public function scopeKycUnverified()
    {
        return $this->where('kv', 0);
    }

    public function scopeKycPending()
    {
        return $this->where('kv', 2);
    }

    public function scopeEmailVerified()
    {
        return $this->where('ev', 1);
    }

    public function scopeMobileVerified()
    {
        return $this->where('sv', 1);
    }

    public function scopeWithBalance()
    {
        return $this->where('balance','>', 0);
    }

    public function scopePremium()
    {
        return $this->where('is_premium', 1);
    }

    public function holdWalletTransactions()
    {
        return $this->hasMany(HoldWalletTransaction::class);
    }

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    public function totalHoldBalance(): Attribute
    {
        return new Attribute(
            get: fn () => $this->referral_commission_hold + $this->upgrade_commission_hold + $this->ptc_commission_hold,
        );
    }

    public function availableHoldBalance()
    {
        return $this->holdWalletTransactions()
            ->where('is_transferred', 0)
            ->where('available_date', '<=', now()->toDateString())
            ->sum('hold_amount');
    }

    public function pendingHoldBalance()
    {
        return $this->holdWalletTransactions()
            ->where('is_transferred', 0)
            ->where('available_date', '>', now()->toDateString())
            ->sum('hold_amount');
    }

    /**
     * Get the count of direct referrals (users who signed up using this user's referral)
     */
    public function getDirectReferralCount(): int
    {
        return User::where('ref_by', $this->id)->count();
    }

    /**
     * Get achievement level based on referral count
     * Level 1: 1-49 referrals (30 days wait)
     * Level 2: 50-199 referrals (15 days wait)
     * Level 3: 200+ referrals (instant transfer)
     */
    public function getAchievementLevel(): int
    {
        $referralCount = $this->total_referrals_count ?? $this->getDirectReferralCount();
        return HoldWalletSetting::calculateAchievementLevel($referralCount);
    }

    /**
     * Get achievement configuration for current level
     */
    public function getAchievementConfig(): array
    {
        $config = HoldWalletSetting::getAchievementConfig();
        $level = $this->getAchievementLevel();
        return $config[$level] ?? $config[1];
    }

    /**
     * Get hold days based on current achievement level
     */
    public function getHoldDays(): int
    {
        return $this->getAchievementConfig()['hold_days'];
    }

    /**
     * Check if user can transfer from hold wallet now
     */
    public function canTransferFromHoldWallet(): array
    {
        $referralCount = $this->total_referrals_count ?? $this->getDirectReferralCount();
        $level = $this->getAchievementLevel();
        $config = $this->getAchievementConfig();
        $holdDays = $config['hold_days'];

        // Level 3 (200+ referrals): Instant transfer
        if ($level >= 3) {
            return [
                'can_transfer' => true,
                'reason' => 'আপনি গোল্ড লেভেলে আছেন। যেকোনো সময় ট্রান্সফার করতে পারবেন।',
                'reason_en' => 'You are at Gold level. You can transfer anytime.',
                'wait_days' => 0,
                'next_transfer_date' => null,
            ];
        }

        // Check last transfer date
        $lastTransfer = $this->last_transfer_date;

        if (!$lastTransfer) {
            // First transfer - check if user has been registered for at least holdDays
            $daysSinceRegistration = now()->diffInDays($this->created_at);
            if ($daysSinceRegistration >= $holdDays) {
                return [
                    'can_transfer' => true,
                    'reason' => 'আপনি ট্রান্সফার করতে পারবেন।',
                    'reason_en' => 'You can transfer now.',
                    'wait_days' => 0,
                    'next_transfer_date' => now()->toDateString(),
                ];
            }
            $remainingDays = $holdDays - $daysSinceRegistration;
            return [
                'can_transfer' => false,
                'reason' => "প্রথম ট্রান্সফারের জন্য আরও {$remainingDays} দিন অপেক্ষা করুন।",
                'reason_en' => "Wait {$remainingDays} more days for your first transfer.",
                'wait_days' => $remainingDays,
                'next_transfer_date' => now()->addDays($remainingDays)->toDateString(),
            ];
        }

        // Check days since last transfer
        $daysSinceLastTransfer = now()->diffInDays($lastTransfer);

        if ($daysSinceLastTransfer >= $holdDays) {
            return [
                'can_transfer' => true,
                'reason' => 'আপনি ট্রান্সফার করতে পারবেন।',
                'reason_en' => 'You can transfer now.',
                'wait_days' => 0,
                'next_transfer_date' => now()->toDateString(),
            ];
        }

        $remainingDays = $holdDays - $daysSinceLastTransfer;
        $nextDate = \Carbon\Carbon::parse($lastTransfer)->addDays($holdDays);

        return [
            'can_transfer' => false,
            'reason' => "পরবর্তী ট্রান্সফারের জন্য আরও {$remainingDays} দিন অপেক্ষা করুন।",
            'reason_en' => "Wait {$remainingDays} more days for next transfer.",
            'wait_days' => $remainingDays,
            'next_transfer_date' => $nextDate->toDateString(),
        ];
    }

    /**
     * Get progress to next achievement level
     */
    public function getAchievementProgress(): array
    {
        $referralCount = $this->total_referrals_count ?? $this->getDirectReferralCount();
        $currentLevel = $this->getAchievementLevel();
        $config = HoldWalletSetting::getAchievementConfig();
        $currentConfig = $config[$currentLevel];

        if ($currentLevel >= 3) {
            return [
                'current_level' => $currentLevel,
                'current_config' => $currentConfig,
                'referral_count' => $referralCount,
                'next_level' => null,
                'next_config' => null,
                'referrals_needed' => 0,
                'progress_percent' => 100,
                'is_max_level' => true,
            ];
        }

        $nextLevel = $currentLevel + 1;
        $nextConfig = $config[$nextLevel];
        $referralsNeeded = $nextConfig['min_referrals'] - $referralCount;

        // Calculate progress percentage within current level
        $levelStart = $currentConfig['min_referrals'];
        $levelEnd = $nextConfig['min_referrals'];
        $progressInLevel = $referralCount - $levelStart;
        $levelRange = $levelEnd - $levelStart;
        $progressPercent = min(100, ($progressInLevel / $levelRange) * 100);

        return [
            'current_level' => $currentLevel,
            'current_config' => $currentConfig,
            'referral_count' => $referralCount,
            'next_level' => $nextLevel,
            'next_config' => $nextConfig,
            'referrals_needed' => max(0, $referralsNeeded),
            'progress_percent' => round($progressPercent, 1),
            'is_max_level' => false,
        ];
    }

    /**
     * Update referral count and achievement level
     */
    public function updateAchievementStats(): void
    {
        $this->total_referrals_count = $this->getDirectReferralCount();
        $this->achievement_level = $this->getAchievementLevel();
        $this->save();
    }

    /**
     * Get available hold balance based on achievement level
     * For Level 3: All hold balance is available
     * For Level 1-2: Only transactions past their available_date
     */
    public function getAvailableHoldBalanceByLevel(): float
    {
        $level = $this->getAchievementLevel();

        if ($level >= 3) {
            // Gold level: All hold balance is available
            return $this->holdWalletTransactions()
                ->where('is_transferred', 0)
                ->sum('hold_amount');
        }

        // Other levels: Only transactions past available_date
        return $this->availableHoldBalance();
    }

    /**
     * Relationship with hold wallet transfers
     */
    public function holdWalletTransfers()
    {
        return $this->hasMany(HoldWalletTransfer::class);
    }

    public function unreadNotificationsCount()
    {
        return $this->userNotifications()->where('is_read', 0)->count();
    }

    /**
     * Check if user can withdraw right now
     * Returns: ['can_withdraw' => bool, 'reason' => string, 'type' => 'anytime'|'weekly'|'none']
     */
    public function canWithdrawNow(): array
    {
        // If no active plan, no withdrawal allowed
        if (!$this->runningPlan || !$this->plan) {
            return [
                'can_withdraw' => false,
                'reason' => 'আপনার কোনো সক্রিয় প্ল্যান নেই। উত্তোলনের জন্য প্ল্যান কিনুন।',
                'reason_en' => 'You don\'t have an active plan. Purchase a plan to withdraw.',
                'type' => 'none'
            ];
        }

        $general = gs();
        $plan = $this->plan;

        // Non-premium lifetime withdrawal limit
        $isPremiumPackage = (bool) ($plan->is_premium_package ?? false);
        if (!$isPremiumPackage) {
            $limit = $general->non_premium_withdraw_limit ?? 1000;
            $used = $this->non_premium_withdraw_used ?? 0;
            $remaining = $limit - $used;
            if ($remaining <= 0) {
                return [
                    'can_withdraw' => false,
                    'reason' => 'আপনার নন-প্রিমিয়াম লাইফটাইম উত্তোলন লিমিট শেষ হয়ে গেছে।',
                    'reason_en' => 'Your non-premium lifetime withdrawal limit has been reached.',
                    'type' => 'none'
                ];
            }
        }

        $anytimeLimit = $plan->anytime_withdraw_limit ?? 5;
        $anytimeUsed = $this->anytime_withdraw_used ?? 0;
        $remainingAnytime = max(0, $anytimeLimit - $anytimeUsed);

        // If anytime withdrawals remaining
        if ($remainingAnytime > 0) {
            return [
                'can_withdraw' => true,
                'reason' => "আপনার {$remainingAnytime}টি যেকোনো সময় উত্তোলন বাকি আছে।",
                'reason_en' => "You have {$remainingAnytime} anytime withdrawal(s) remaining.",
                'type' => 'anytime',
                'remaining' => $remainingAnytime,
                'total' => $anytimeLimit
            ];
        }

        // Anytime limit exhausted, check weekly
        if (!$plan->weekly_withdraw_enabled) {
            return [
                'can_withdraw' => false,
                'reason' => 'আপনার সব উত্তোলন সুবিধা শেষ হয়ে গেছে।',
                'reason_en' => 'All withdrawal privileges have been used.',
                'type' => 'none'
            ];
        }

        // Check if can do weekly withdrawal
        $today = now()->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.
        $withdrawDay = $plan->weekly_withdraw_day ?? 0;
        $lastWeeklyWithdraw = $this->last_weekly_withdraw;

        // Calculate days until next withdrawal day
        $daysUntil = ($withdrawDay - $today + 7) % 7;
        if ($daysUntil == 0) {
            // Today is withdrawal day
            // Check if already withdrew this week using ISO week for consistent behavior
            if ($lastWeeklyWithdraw) {
                $lastWithdrawDate = $lastWeeklyWithdraw instanceof \DateTimeInterface
                    ? \Carbon\Carbon::instance($lastWeeklyWithdraw)
                    : \Carbon\Carbon::parse($lastWeeklyWithdraw);
                $isSameIsoWeek = $lastWithdrawDate->isoWeek() == now()->isoWeek()
                    && $lastWithdrawDate->year == now()->year;
                if ($isSameIsoWeek) {
                    return [
                        'can_withdraw' => false,
                        'reason' => 'এই সপ্তাহে আপনার উত্তোলন সম্পন্ন হয়েছে। পরের সপ্তাহে আবার করতে পারবেন।',
                        'reason_en' => 'You have already withdrawn this week. Try again next week.',
                        'type' => 'weekly',
                        'next_date' => now()->next($withdrawDay)->format('d M, Y')
                    ];
                }
            }
            return [
                'can_withdraw' => true,
                'reason' => 'আজ আপনার সাপ্তাহিক উত্তোলনের দিন।',
                'reason_en' => 'Today is your weekly withdrawal day.',
                'type' => 'weekly'
            ];
        }

        $dayNames = ['রবিবার', 'সোমবার', 'মঙ্গলবার', 'বুধবার', 'বৃহস্পতিবার', 'শুক্রবার', 'শনিবার'];
        $dayNamesEn = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $nextDate = now()->next($withdrawDay);

        return [
            'can_withdraw' => false,
            'reason' => "আপনার সাপ্তাহিক উত্তোলনের দিন {$dayNames[$withdrawDay]}। পরবর্তী তারিখ: {$nextDate->format('d M, Y')}",
            'reason_en' => "Your weekly withdrawal day is {$dayNamesEn[$withdrawDay]}. Next date: {$nextDate->format('d M, Y')}",
            'type' => 'weekly',
            'next_date' => $nextDate->format('d M, Y'),
            'days_until' => $daysUntil
        ];
    }

    /**
     * Get withdrawal status info for display
     */
    public function getWithdrawStatusAttribute(): array
    {
        return $this->canWithdrawNow();
    }

    /**
     * Use one anytime withdrawal
     */
    public function useAnytimeWithdrawal(): bool
    {
        if (!$this->plan) return false;

        // If the tracking column doesn't exist yet, avoid crashing.
        // NOTE: limits won't be enforced until migrations are applied.
        if (!self::usersTableHasColumn('anytime_withdraw_used')) {
            return true;
        }

        $anytimeLimit = $this->plan->anytime_withdraw_limit ?? 5;
        if ($this->anytime_withdraw_used < $anytimeLimit) {
            $this->anytime_withdraw_used += 1;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Record weekly withdrawal
     */
    public function recordWeeklyWithdrawal(): void
    {
        if (!self::usersTableHasColumn('last_weekly_withdraw')) {
            return;
        }
        // Column type is DATE, so store YYYY-MM-DD
        $this->last_weekly_withdraw = now()->toDateString();
        $this->save();
    }

    /**
     * Reset withdrawal limits (called when user buys new plan)
     */
    public function resetWithdrawalLimits(): void
    {
        $dirty = false;

        if (self::usersTableHasColumn('anytime_withdraw_used')) {
            $this->anytime_withdraw_used = 0;
            $dirty = true;
        }
        if (self::usersTableHasColumn('last_weekly_withdraw')) {
            $this->last_weekly_withdraw = null;
            $dirty = true;
        }
        if (self::usersTableHasColumn('plan_purchase_date')) {
            $this->plan_purchase_date = now();
            $dirty = true;
        }

        if ($dirty) {
            $this->save();
        }
    }

}
