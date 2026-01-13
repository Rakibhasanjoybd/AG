<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HoldWalletTransfer extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'fee',
        'net_amount',
        'achievement_level',
        'referral_count_at_transfer',
        'trx',
        'details',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'fee' => 'decimal:8',
        'net_amount' => 'decimal:8',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
