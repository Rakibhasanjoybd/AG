<?php

namespace App\Services;

use App\Models\User;
use App\Models\Referral;
use App\Models\CommissionLog;
use App\Models\Transaction;
use App\Models\HoldWalletTransaction;
use App\Models\HoldWalletSetting;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

/**
 * CommissionService handles all referral commission calculations with proper transaction locking.
 * This prevents race conditions where the same commission could be paid twice or lost entirely.
 */
class CommissionService
{
    // Commission type constants for consistency
    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_PLAN_SUBSCRIBE = 'plan_subscribe';
    public const TYPE_PTC_VIEW = 'ptc_view';
    public const TYPE_REFERRAL = 'referral';

    // Split percentages
    public const INSTANT_PERCENT = 0.40;  // 40% instant
    public const HOLD_PERCENT = 0.60;     // 60% held for 30 days
    public const HOLD_DAYS = 30;

    /**
     * Map commission types to their corresponding database setting keys
     */
    private array $settingKeys = [
        self::TYPE_DEPOSIT => 'deposit_commission',
        self::TYPE_PLAN_SUBSCRIBE => 'plan_subscribe_commission',
        self::TYPE_PTC_VIEW => 'ptc_view_commission',
        self::TYPE_REFERRAL => 'referral_commission',
    ];

    /**
     * Map commission types to their hold balance columns
     */
    private array $holdColumns = [
        self::TYPE_DEPOSIT => 'upgrade_commission_hold',
        self::TYPE_PLAN_SUBSCRIBE => 'upgrade_commission_hold',
        self::TYPE_PTC_VIEW => 'ptc_commission_hold',
        self::TYPE_REFERRAL => 'referral_commission_hold',
    ];

    /**
     * Process multi-level commission for a transaction
     *
     * @param User $referee The user who triggered the commission (the one being referred)
     * @param float $amount The transaction amount to calculate commission from
     * @param string $commissionType One of the TYPE_* constants
     * @param string $trx Transaction reference
     * @return Collection Collection of commission logs created
     */
    public function processLevelCommission(
        User $referee,
        float $amount,
        string $commissionType,
        string $trx
    ): Collection {
        $general = gs();

        // Get the setting key for this commission type
        $settingKey = $this->settingKeys[$commissionType] ?? $commissionType . '_commission';

        // Check if this commission type is enabled
        if (!$general->$settingKey) {
            return collect();
        }

        // Get all referral levels for this commission type
        $levels = Referral::where('commission_type', $settingKey)
            ->orderBy('level')
            ->get()
            ->keyBy('level');

        if ($levels->isEmpty()) {
            return collect();
        }

        $currentUser = $referee;
        $level = 1;
        $commissionLogs = collect();

        while ($level <= $levels->count()) {
            $refererId = $currentUser->ref_by;

            if (!$refererId) {
                break;
            }

            try {
                // Process each level in its own transaction for isolation
                $log = $this->processLevelForUser(
                    $refererId,
                    $referee,
                    $amount,
                    $commissionType,
                    $trx,
                    $levels->get($level),
                    $level
                );

                if ($log) {
                    $commissionLogs->push($log);
                }
            } catch (\Exception $e) {
                // Log error but continue processing other levels
                Log::error("Commission processing failed for level {$level}: " . $e->getMessage(), [
                    'referer_id' => $refererId,
                    'referee_id' => $referee->id,
                    'amount' => $amount,
                    'type' => $commissionType,
                    'trx' => $trx,
                ]);
            }

            // Get next user in chain (fresh query to avoid stale data)
            $currentUser = User::find($refererId);
            if (!$currentUser) {
                break;
            }
            $level++;
        }

        return $commissionLogs;
    }

    /**
     * Process commission for a single level/user
     *
     * @param int $refererId The user receiving the commission
     * @param User $referee The user who triggered the commission
     * @param float $amount Base amount for commission calculation
     * @param string $commissionType Commission type constant
     * @param string $trx Transaction reference
     * @param Referral|null $levelConfig The referral configuration for this level
     * @param int $level The referral level number
     * @return CommissionLog|null
     */
    private function processLevelForUser(
        int $refererId,
        User $referee,
        float $amount,
        string $commissionType,
        string $trx,
        ?Referral $levelConfig,
        int $level
    ): ?CommissionLog {
        if (!$levelConfig) {
            return null;
        }

        return DB::transaction(function () use ($refererId, $referee, $amount, $commissionType, $trx, $levelConfig, $level) {
            // Lock the referer for update to prevent race conditions
            $referer = User::lockForUpdate()->find($refererId);

            if (!$referer) {
                return null;
            }

            // Check if user has an active plan
            $plan = $referer->plan;
            if (!$plan) {
                return null;
            }

            // Check if user's plan allows this referral level
            if ($level > $plan->ref_level) {
                return null;
            }

            // Calculate commission
            $totalCommission = ($amount * $levelConfig->percent) / 100;
            $instantAmount = $totalCommission * self::INSTANT_PERCENT;
            $holdAmount = $totalCommission * self::HOLD_PERCENT;

            // CommissionLog idempotency key (per credited user per source)
            // Use the mapped setting key to stay consistent with existing CommissionLog.type values.
            $settingKey = $this->settingKeys[$commissionType] ?? $commissionType . '_commission';
            $sourceType = $settingKey;
            $sourceId = $trx;

            // Create commission log FIRST as the idempotency guard.
            // If a duplicate exists (unique index), we skip all side-effects.
            $commissionLog = $this->createCommissionLog(
                $referer,
                $referee,
                $totalCommission,
                $instantAmount,
                $holdAmount,
                $commissionType,
                $trx,
                $level,
                $sourceType,
                $sourceId
            );

            if (!$commissionLog) {
                return null;
            }

            // Get the hold column for this commission type
            $holdColumn = $this->holdColumns[$commissionType] ?? 'referral_commission_hold';

            // Update balances atomically
            $referer->balance += $instantAmount;
            $referer->$holdColumn += $holdAmount;
            $referer->save();

            // Create hold wallet transaction record
            $this->createHoldWalletTransaction(
                $referer,
                $referee,
                $totalCommission,
                $instantAmount,
                $holdAmount,
                $commissionType,
                $trx,
                $level
            );

            // Create instant transaction record
            $this->createInstantTransaction(
                $referer,
                $referee,
                $instantAmount,
                $trx,
                $level
            );

            // Create user notification
            $this->createNotification(
                $referer,
                $referee,
                $totalCommission,
                $instantAmount,
                $holdAmount
            );

            // Send notification via configured channels
            $this->sendNotification(
                $referer,
                $referee,
                $totalCommission,
                $instantAmount,
                $holdAmount,
                $commissionType,
                $trx,
                $level
            );

            return $commissionLog;
        }, 3); // 3 retry attempts for deadlock handling
    }

    /**
     * Create hold wallet transaction record
     * Uses dynamic hold days based on referer's achievement level
     */
    private function createHoldWalletTransaction(
        User $referer,
        User $referee,
        float $totalCommission,
        float $instantAmount,
        float $holdAmount,
        string $commissionType,
        string $trx,
        int $level
    ): HoldWalletTransaction {
        // Get dynamic hold days based on referer's achievement level
        $holdDays = $referer->getHoldDays();
        $achievementLevel = $referer->getAchievementLevel();

        return HoldWalletTransaction::create([
            'user_id' => $referer->id,
            'amount' => $totalCommission,
            'instant_amount' => $instantAmount,
            'hold_amount' => $holdAmount,
            'commission_type' => $commissionType,
            'from_user_id' => $referee->id,
            'available_date' => now()->addDays($holdDays)->toDateString(),
            'achievement_level' => $achievementLevel,
            'hold_days' => $holdDays,
            'trx' => $trx,
            'source_description' => $this->ordinal($level) . ' level referral commission from ' . $referee->username,
        ]);
    }

    /**
     * Create instant transaction record
     */
    private function createInstantTransaction(
        User $referer,
        User $referee,
        float $instantAmount,
        string $trx,
        int $level
    ): Transaction {
        return Transaction::create([
            'user_id' => $referer->id,
            'amount' => $instantAmount,
            'post_balance' => $referer->balance,
            'charge' => 0,
            'trx_type' => '+',
            'details' => $this->ordinal($level) . ' level referral commission (40% instant) from ' . $referee->username,
            'remark' => 'referral_commission',
            'trx' => $trx,
        ]);
    }

    /**
     * Create commission log record
     */
    private function createCommissionLog(
        User $referer,
        User $referee,
        float $totalCommission,
        float $instantAmount,
        float $holdAmount,
        string $commissionType,
        string $trx,
        int $level,
        ?string $sourceType = null,
        ?string $sourceId = null
    ): ?CommissionLog {
        $settingKey = $this->settingKeys[$commissionType] ?? $commissionType . '_commission';

        // Cache schema capability check to avoid repeated queries.
        static $commissionLogsHaveSourceCols = null;
        if ($commissionLogsHaveSourceCols === null) {
            $commissionLogsHaveSourceCols = Schema::hasColumn('commission_logs', 'source_type')
                && Schema::hasColumn('commission_logs', 'source_id');
        }

        // Best-effort fallback when the migration hasn't run yet.
        if (!$commissionLogsHaveSourceCols) {
            $already = CommissionLog::where('to_id', $referer->id)
                ->where('trx', $trx)
                ->where('type', $settingKey)
                ->exists();
            if ($already) {
                return null;
            }
        }

        $data = [
            'to_id' => $referer->id,
            'from_id' => $referee->id,
            'level' => $level,
            'amount' => $totalCommission,
            'details' => $this->ordinal($level) . ' level referral commission from ' . $referee->username .
                        ' (40% instant: ' . showAmount($instantAmount) . ', 60% held: ' . showAmount($holdAmount) . ')',
            'type' => $settingKey,
            'trx' => $trx,
        ];

        if ($commissionLogsHaveSourceCols) {
            $data['source_type'] = $sourceType ?? $settingKey;
            $data['source_id'] = $sourceId ?? $trx;
        }

        try {
            return CommissionLog::create($data);
        } catch (\Illuminate\Database\QueryException $qe) {
            // MySQL duplicate key: 1062
            if ($commissionLogsHaveSourceCols && (int)($qe->errorInfo[1] ?? 0) === 1062) {
                return null;
            }
            throw $qe;
        }
    }

    /**
     * Create user notification
     */
    private function createNotification(
        User $referer,
        User $referee,
        float $totalCommission,
        float $instantAmount,
        float $holdAmount
    ): UserNotification {
        return UserNotification::create([
            'user_id' => $referer->id,
            'title' => 'Commission Received',
            'message' => 'You received ' . showAmount($totalCommission) . ' commission from ' . $referee->username .
                        '. ' . showAmount($instantAmount) . ' added to main balance, ' . showAmount($holdAmount) . ' held for ' . self::HOLD_DAYS . ' days.',
            'type' => 'commission',
        ]);
    }

    /**
     * Send notification via configured channels (email, SMS, etc.)
     */
    private function sendNotification(
        User $referer,
        User $referee,
        float $totalCommission,
        float $instantAmount,
        float $holdAmount,
        string $commissionType,
        string $trx,
        int $level
    ): void {
        try {
            notify($referer, 'REFERRAL_COMMISSION', [
                'amount' => showAmount($totalCommission),
                'instant_amount' => showAmount($instantAmount),
                'hold_amount' => showAmount($holdAmount),
                'post_balance' => showAmount($referer->balance),
                'trx' => $trx,
                'level' => $this->ordinal($level),
                'type' => ucfirst(str_replace('_', ' ', $commissionType))
            ]);
        } catch (\Exception $e) {
            Log::warning("Failed to send commission notification: " . $e->getMessage());
        }
    }

    /**
     * Get ordinal representation of a number (1st, 2nd, 3rd, etc.)
     */
    private function ordinal(int $number): string
    {
        $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];

        if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
            return $number . 'th';
        }

        return $number . $ends[$number % 10];
    }

    /**
     * Get commission statistics for a user
     *
     * @param int $userId
     * @return array
     */
    public function getCommissionStats(int $userId): array
    {
        return [
            'total_received' => CommissionLog::where('to_id', $userId)->sum('amount'),
            'total_given' => CommissionLog::where('from_id', $userId)->sum('amount'),
            'by_type' => CommissionLog::where('to_id', $userId)
                ->selectRaw('type, SUM(amount) as total')
                ->groupBy('type')
                ->pluck('total', 'type')
                ->toArray(),
            'by_level' => CommissionLog::where('to_id', $userId)
                ->selectRaw('level, SUM(amount) as total')
                ->groupBy('level')
                ->orderBy('level')
                ->pluck('total', 'level')
                ->toArray(),
        ];
    }
}
