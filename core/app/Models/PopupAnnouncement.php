<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PopupAnnouncement extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'show_once' => 'boolean',
        'show_to_guests' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * Get the users targeted by this popup
     */
    public function targetUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'popup_announcement_user', 'popup_announcement_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Get users who have viewed this popup
     */
    public function viewedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'popup_announcement_views', 'popup_announcement_id', 'user_id')
            ->withPivot('viewed_at')
            ->withTimestamps();
    }

    /**
     * Scope for active popups
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope for currently valid popups (within date range)
     */
    public function scopeValid($query)
    {
        $now = now();
        return $query->where(function($q) use ($now) {
            $q->whereNull('start_date')
              ->orWhere('start_date', '<=', $now);
        })->where(function($q) use ($now) {
            $q->whereNull('end_date')
              ->orWhere('end_date', '>=', $now);
        });
    }

    /**
     * Scope for popups targeting all users
     */
    public function scopeForAllUsers($query)
    {
        return $query->where('target_type', 'all');
    }

    /**
     * Scope for popups targeting specific users
     */
    public function scopeForSpecificUsers($query)
    {
        return $query->where('target_type', 'specific');
    }

    /**
     * Check if popup should be shown to a specific user
     */
    public function shouldShowToUser(?User $user = null): bool
    {
        // Check if popup is active and valid
        if (!$this->status) {
            return false;
        }

        $now = now();
        
        // Check date range
        if ($this->start_date && $this->start_date > $now) {
            return false;
        }
        if ($this->end_date && $this->end_date < $now) {
            return false;
        }

        // Guest user handling
        if (!$user) {
            return $this->show_to_guests;
        }

        // If target_type is 'all', show to all logged-in users
        if ($this->target_type === 'all') {
            // Check if user already viewed (if show_once is enabled)
            if ($this->show_once) {
                return !$this->viewedByUsers()->where('user_id', $user->id)->exists();
            }
            return true;
        }

        // If target_type is 'specific', check if user is in target list
        if ($this->target_type === 'specific') {
            $isTargeted = $this->targetUsers()->where('user_id', $user->id)->exists();
            if (!$isTargeted) {
                return false;
            }

            // Check if user already viewed (if show_once is enabled)
            if ($this->show_once) {
                return !$this->viewedByUsers()->where('user_id', $user->id)->exists();
            }
            return true;
        }

        return false;
    }

    /**
     * Mark popup as viewed by user
     */
    public function markAsViewedBy(User $user): void
    {
        if (!$this->viewedByUsers()->where('user_id', $user->id)->exists()) {
            $this->viewedByUsers()->attach($user->id, ['viewed_at' => now()]);
        }
    }

    /**
     * Get image URL
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return getImage('assets/images/popup/' . $this->image);
        }
        return null;
    }
}
