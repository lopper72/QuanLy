<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BehaviorLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'child_id',
        'training_session_id',
        'training_session_item_id',
        'behavior_type',
        'severity',
        'trigger',
        'response',
        'note',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function trainingSession()
    {
        return $this->belongsTo(TrainingSession::class);
    }

    public function trainingSessionItem()
    {
        return $this->belongsTo(TrainingSessionItem::class);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('recorded_at', 'desc');
    }
}
