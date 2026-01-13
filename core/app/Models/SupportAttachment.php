<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportAttachment extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    public function supportMessage()
    {
        return $this->belongsTo(SupportMessage::class,'support_message_id');
    }
}
