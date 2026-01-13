<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedBagClaim extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_winner' => 'boolean',
        'is_fraudulent' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function redBag()
    {
        return $this->belongsTo(RedBag::class);
    }

    public static function checkDeviceFraud($deviceId, $userId)
    {
        if (empty($deviceId)) {
            return ['is_fraud' => false, 'reason' => null];
        }

        // Check if device was used by different user today
        $otherUserClaim = self::where('device_id', $deviceId)
            ->where('user_id', '!=', $userId)
            ->whereDate('created_at', today())
            ->first();

        if ($otherUserClaim) {
            return [
                'is_fraud' => true,
                'reason' => 'Device used by multiple accounts'
            ];
        }

        // Check device claim velocity (too many claims in short time)
        $recentClaims = self::where('device_id', $deviceId)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        if ($recentClaims >= 3) {
            return [
                'is_fraud' => true,
                'reason' => 'Too many claims from device'
            ];
        }

        return ['is_fraud' => false, 'reason' => null];
    }

    public static function checkIpFraud($ipAddress, $userId)
    {
        if (empty($ipAddress)) {
            return ['is_fraud' => false, 'reason' => null];
        }

        // Check if IP has too many different users today
        $uniqueUsers = self::where('ip_address', $ipAddress)
            ->whereDate('created_at', today())
            ->distinct('user_id')
            ->count('user_id');

        if ($uniqueUsers >= 5) {
            return [
                'is_fraud' => true,
                'reason' => 'IP address used by too many accounts'
            ];
        }

        // Check IP claim velocity
        $recentClaims = self::where('ip_address', $ipAddress)
            ->where('user_id', '!=', $userId)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->count();

        if ($recentClaims >= 5) {
            return [
                'is_fraud' => true,
                'reason' => 'Too many claims from IP'
            ];
        }

        return ['is_fraud' => false, 'reason' => null];
    }

    public static function performFraudCheck($userId, $deviceId, $ipAddress)
    {
        $deviceCheck = self::checkDeviceFraud($deviceId, $userId);
        if ($deviceCheck['is_fraud']) {
            return $deviceCheck;
        }

        $ipCheck = self::checkIpFraud($ipAddress, $userId);
        if ($ipCheck['is_fraud']) {
            return $ipCheck;
        }

        return ['is_fraud' => false, 'reason' => null];
    }
}
