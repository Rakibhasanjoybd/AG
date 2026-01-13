<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppDownload extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'status' => 'boolean',
        'force_update' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeAndroid($query)
    {
        return $query->where('platform', 'android');
    }

    public function scopeIos($query)
    {
        return $query->where('platform', 'ios');
    }

    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }
}
