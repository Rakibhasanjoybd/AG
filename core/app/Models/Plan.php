<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:8',
        'ptc_view_amount' => 'decimal:8',
        'status' => 'boolean',
        'image' => 'string',
        'anytime_withdraw_limit' => 'integer',
        'weekly_withdraw_day' => 'integer',
        'weekly_withdraw_enabled' => 'boolean',
        'package_number' => 'integer',
        'is_premium_package' => 'boolean',
        'commission_level_a_rate' => 'decimal:2',
        'commission_level_a_max' => 'decimal:2',
        'commission_level_b_rate' => 'decimal:2',
        'commission_level_b_max' => 'decimal:2',
        'commission_level_c_rate' => 'decimal:2',
        'commission_level_c_max' => 'decimal:2',
        'task_commission_a_rate' => 'decimal:2',
        'task_commission_b_rate' => 'decimal:2',
        'task_commission_c_rate' => 'decimal:2',
        'features' => 'array',
        'is_featured' => 'boolean',
        'is_popular' => 'boolean',
    ];

    /**
     * Get plan image URL
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('assets/images/plan/' . $this->image);
        }
        return null;
    }

    /**
     * Get users subscribed to this plan.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scope: only active plans
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Get active users count for this plan
     */
    public function getActiveUsersCountAttribute(): int
    {
        return $this->users()
            ->where('expire_date', '>', now())
            ->count();
    }

    /**
     * Calculate commission for Level A referrals
     */
    public function calculateLevelACommission($amount = null)
    {
        $amount = $amount ?? $this->price;
        $commission = ($amount * $this->commission_level_a_rate) / 100;

        if ($this->commission_level_a_max && $commission > $this->commission_level_a_max) {
            return $this->commission_level_a_max;
        }

        return $commission;
    }

    /**
     * Calculate commission for Level B referrals
     */
    public function calculateLevelBCommission($amount = null)
    {
        $amount = $amount ?? $this->price;
        $commission = ($amount * $this->commission_level_b_rate) / 100;

        if ($this->commission_level_b_max && $commission > $this->commission_level_b_max) {
            return $this->commission_level_b_max;
        }

        return $commission;
    }

    /**
     * Calculate commission for Level C referrals
     */
    public function calculateLevelCCommission($amount = null)
    {
        $amount = $amount ?? $this->price;
        $commission = ($amount * $this->commission_level_c_rate) / 100;

        if ($this->commission_level_c_max && $commission > $this->commission_level_c_max) {
            return $this->commission_level_c_max;
        }

        return $commission;
    }

    /**
     * Get color scheme classes
     */
    public function getColorClassAttribute()
    {
        $schemes = [
            'green' => 'plan-green',
            'blue' => 'plan-blue',
            'orange' => 'plan-orange',
            'red' => 'plan-red',
        ];

        return $schemes[$this->color_scheme] ?? 'plan-green';
    }

    /**
     * Scope: filter by region
     */
    public function scopeByRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    /**
     * Scope: featured plans
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: popular plans
     */
    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    /**
     * Scope: ordered for display
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
