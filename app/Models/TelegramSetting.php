<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'bot_token',
        'bot_username',
        'webhook_secret',
        'default_chat_id',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    protected $hidden = [
        'bot_token',
    ];

    public static function current(): self
    {
        return self::query()->firstOrCreate([], [
            'bot_token' => config('services.telegram.bot_token'),
            'bot_username' => config('services.telegram.bot_username'),
            'webhook_secret' => config('services.telegram.webhook_secret'),
            'enabled' => false,
        ]);
    }

    public function maskedToken(): ?string
    {
        if (blank($this->bot_token)) {
            return null;
        }

        return '••••••••' . substr($this->bot_token, -4);
    }
}
