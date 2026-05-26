<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramMealSuggestionLog extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';
    public const STATUS_PREPARED = 'prepared';

    protected $fillable = [
        'child_id',
        'telegram_chat_id',
        'suggestion_date',
        'meal_plan_item_id',
        'message_text',
        'sent_at',
        'status',
        'error_message',
    ];

    protected $casts = [
        'suggestion_date' => 'date',
        'sent_at' => 'datetime',
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function mealPlanItem()
    {
        return $this->belongsTo(MealPlanItem::class);
    }
}
