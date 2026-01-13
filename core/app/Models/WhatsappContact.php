<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'department',
        'phone_number',
        'profile_image',
        'message_format',
        'description',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Scope to get only active contacts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get contacts ordered by display_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc');
    }

    /**
     * Get the WhatsApp URL with pre-filled message
     */
    public function getWhatsappUrlAttribute()
    {
        $phone = preg_replace('/[^0-9]/', '', $this->phone_number);
        $message = $this->message_format ? urlencode($this->message_format) : '';
        return "https://wa.me/{$phone}?text={$message}";
    }

    /**
     * Get profile image URL
     */
    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image) {
            return asset('assets/images/whatsapp/' . $this->profile_image);
        }
        return asset('assets/images/default-avatar.png');
    }
}

