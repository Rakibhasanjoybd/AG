<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PtcReview extends Model
{
    protected $fillable = ['ptc_id', 'user_id', 'rating', 'comment'];

    public function ptc()
    {
        return $this->belongsTo(Ptc::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
