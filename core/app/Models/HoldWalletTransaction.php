<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HoldWalletTransaction extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'available_date' => 'date',
        'transferred_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function scopeAvailableForTransfer($query)
    {
        return $query->where('is_transferred', 0)
            ->where('available_date', '<=', now()->toDateString());
    }

    public function scopePending($query)
    {
        return $query->where('is_transferred', 0)
            ->where('available_date', '>', now()->toDateString());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('commission_type', $type);
    }
}
