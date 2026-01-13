<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class HoldWalletSetting extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    /**
     * Get a setting value by key with caching
     */
    public static function getValue(string $key, $default = null)
    {
        return Cache::remember("hold_wallet_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value
     */
    public static function setValue(string $key, $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        Cache::forget("hold_wallet_setting_{$key}");
    }

    /**
     * Get all settings as key-value array
     */
    public static function getAllSettings(): array
    {
        return Cache::remember('hold_wallet_all_settings', 3600, function () {
            return self::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Get achievement level configuration
     */
    public static function getAchievementConfig(): array
    {
        $settings = self::getAllSettings();

        return [
            1 => [
                'min_referrals' => (int) ($settings['level_1_min_referrals'] ?? 1),
                'max_referrals' => (int) ($settings['level_1_max_referrals'] ?? 49),
                'hold_days' => (int) ($settings['level_1_hold_days'] ?? 30),
                'name' => 'স্টার্টার',
                'name_en' => 'Starter',
                'icon' => 'fas fa-seedling',
                'color' => '#9333ea',
            ],
            2 => [
                'min_referrals' => (int) ($settings['level_2_min_referrals'] ?? 50),
                'max_referrals' => (int) ($settings['level_2_max_referrals'] ?? 199),
                'hold_days' => (int) ($settings['level_2_hold_days'] ?? 15),
                'name' => 'সিলভার',
                'name_en' => 'Silver',
                'icon' => 'fas fa-medal',
                'color' => '#6b7280',
            ],
            3 => [
                'min_referrals' => (int) ($settings['level_3_min_referrals'] ?? 200),
                'max_referrals' => null, // unlimited
                'hold_days' => (int) ($settings['level_3_hold_days'] ?? 0),
                'name' => 'গোল্ড',
                'name_en' => 'Gold',
                'icon' => 'fas fa-crown',
                'color' => '#f59e0b',
            ],
        ];
    }

    /**
     * Get transfer fee configuration
     */
    public static function getTransferFeeConfig(): array
    {
        $settings = self::getAllSettings();

        return [
            'type' => $settings['transfer_fee_type'] ?? 'percent',
            'amount' => (float) ($settings['transfer_fee_amount'] ?? 2),
            'min_transfer' => (float) ($settings['min_transfer_amount'] ?? 100),
        ];
    }

    /**
     * Get commission split configuration
     */
    public static function getCommissionSplitConfig(): array
    {
        $settings = self::getAllSettings();

        return [
            'instant_percent' => (float) ($settings['instant_percent'] ?? 40) / 100,
            'hold_percent' => (float) ($settings['hold_percent'] ?? 60) / 100,
        ];
    }

    /**
     * Calculate achievement level from referral count
     */
    public static function calculateAchievementLevel(int $referralCount): int
    {
        $config = self::getAchievementConfig();

        if ($referralCount >= $config[3]['min_referrals']) {
            return 3;
        } elseif ($referralCount >= $config[2]['min_referrals']) {
            return 2;
        }
        return 1;
    }

    /**
     * Get hold days for achievement level
     */
    public static function getHoldDaysForLevel(int $level): int
    {
        $config = self::getAchievementConfig();
        return $config[$level]['hold_days'] ?? 30;
    }

    /**
     * Calculate transfer fee
     */
    public static function calculateTransferFee(float $amount): float
    {
        $config = self::getTransferFeeConfig();

        if ($config['type'] === 'fixed') {
            return $config['amount'];
        }

        return ($amount * $config['amount']) / 100;
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget('hold_wallet_all_settings');
        $settings = self::all();
        foreach ($settings as $setting) {
            Cache::forget("hold_wallet_setting_{$setting->key}");
        }
    }
}
