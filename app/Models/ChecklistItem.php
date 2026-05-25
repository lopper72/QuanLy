<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    use HasFactory;

    public const STATUS_NOT_STARTED = 'not_started';
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REFUSED = 'refused';
    public const STATUS_MISSED = 'missed';

    protected $fillable = [
        'daily_checklist_id',
        'training_session_item_id',
        'carried_over_from_id',
        'status',
        'performance_result',
        'parent_note',
        'completed_at',
        'carried_over_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'carried_over_at' => 'datetime',
    ];

    public function dailyChecklist()
    {
        return $this->belongsTo(DailyChecklist::class);
    }

    public function trainingSessionItem()
    {
        return $this->belongsTo(TrainingSessionItem::class);
    }

    public function carriedOverFrom()
    {
        return $this->belongsTo(ChecklistItem::class, 'carried_over_from_id');
    }
}
