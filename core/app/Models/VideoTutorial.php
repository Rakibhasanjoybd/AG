<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoTutorial extends Model
{
    protected $guarded = ['id'];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('lesson_number', 'asc');
    }
}
