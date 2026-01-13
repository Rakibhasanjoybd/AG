<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedBagDevice extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_blocked' => 'boolean',
    ];

    public function firstUser()
    {
        return $this->belongsTo(User::class, 'first_user_id');
    }

    public static function recordDevice($deviceId, $userId)
    {
        if (empty($deviceId)) {
            return null;
        }

        $device = self::firstOrCreate(
            ['device_id' => $deviceId],
            ['first_user_id' => $userId]
        );

        $device->increment('claim_count');

        return $device;
    }

    public static function isBlocked($deviceId)
    {
        if (empty($deviceId)) {
            return false;
        }

        $device = self::where('device_id', $deviceId)->first();
        return $device ? $device->is_blocked : false;
    }

    public static function blockDevice($deviceId, $reason = null)
    {
        return self::where('device_id', $deviceId)->update([
            'is_blocked' => true,
            'block_reason' => $reason
        ]);
    }
}
