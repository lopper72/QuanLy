<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'child_id',
        'session_date',
        'scheduled_time',
        'status',
        'total_minutes',
        'notes',
        'closed_at',
        'auto_closed_reason',
    ];

    protected $casts = [
        'session_date' => 'date',
        'scheduled_time' => 'string',
        'total_minutes' => 'integer',
        'closed_at' => 'datetime',
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function items()
    {
        return $this->hasMany(TrainingSessionItem::class);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('session_date', '>=', today());
    }

    public function scopeUnfinished($query)
    {
        return $query->whereIn('status', ['pending', 'planned', 'in_progress']);
    }
}
