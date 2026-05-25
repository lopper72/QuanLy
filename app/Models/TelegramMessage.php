<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramMessage extends Model
{
    use HasFactory;

    public const DIRECTION_INBOUND = 'inbound';
    public const DIRECTION_OUTBOUND = 'outbound';

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_RECEIVED = 'received';

    protected $fillable = [
        'direction',
        'telegram_chat_id',
        'telegram_user_id',
        'telegram_username',
        'message_type',
        'message_text',
        'callback_data',
        'action_status',
        'payload_json',
        'delivery_status',
        'sent_at',
        'received_at',
        'related_child_id',
        'related_training_id',
    ];

    protected $casts = [
        'payload_json' => 'array',
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function child()
    {
        return $this->belongsTo(Child::class, 'related_child_id');
    }

    public function trainingSession()
    {
        return $this->belongsTo(TrainingSession::class, 'related_training_id');
    }
}
