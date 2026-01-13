<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PremiumReferralCommission extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:8',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
