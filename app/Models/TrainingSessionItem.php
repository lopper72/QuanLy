<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingSessionItem extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REFUSED = 'refused';
    public const STATUS_MISSED = 'missed';
    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'training_session_id',
        'exercise_id',
        'sort_order',
        'duration_minutes',
        'completion_status',
        'therapist_note',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'duration_minutes' => 'integer',
    ];

    public function trainingSession()
    {
        return $this->belongsTo(TrainingSession::class);
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }

    public function checklistItem()
    {
        return $this->hasOne(ChecklistItem::class);
    }
}
