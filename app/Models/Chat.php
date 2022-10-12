<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function participants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ChatParticipant::class, 'chat_id', 'id');
    }

    public function message(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ChatMessage::class, 'chat_id', 'id');
    }

    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class, 'chat_id', 'id')->latest('updated_at');
    }

    public static function scopeHasParticipant($query, $userId)
    {
        return $query->whereHas('participants', function ($q) use ($userId){
            $q->where('user_id', $userId);
        });
    }
}
