<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramReminderLog extends Model
{
    use HasFactory;

    public const TYPE_TRAINING = 'training';
    public const TYPE_MEAL = 'meal';
    public const TYPE_SUPPLEMENT = 'supplement';

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'child_id',
        'telegram_chat_id',
        'reminder_type',
        'related_type',
        'related_id',
        'scheduled_for',
        'reminder_due_at',
        'sent_at',
        'status',
        'error_message',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'reminder_due_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }
}
