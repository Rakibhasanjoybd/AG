<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgcoBrand extends Model
{
    protected $table = 'agco_brands';
    
    protected $fillable = [
        'name',
        'image',
        'url',
        'order',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'order' => 'integer'
    ];

    /**
     * Scope to get only active brands
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope to order brands by order column
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'ASC');
    }

    /**
     * Get the brand image path
     */
    public function getImagePathAttribute()
    {
        return 'assets/images/brands/' . $this->image;
    }
}
