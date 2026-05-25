<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'telegram_chat_id',
        'telegram_user_id',
        'telegram_username',
        'display_name',
        'last_seen_at',
        'is_active',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function messages()
    {
        return $this->hasMany(TelegramMessage::class, 'telegram_chat_id', 'telegram_chat_id');
    }
}
