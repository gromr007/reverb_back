<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = ['id', 'from_id', 'chat_room_id', 'text'];

    public function from()
    {
        return $this->belongsTo(User::class, 'from_id');
    }
}
