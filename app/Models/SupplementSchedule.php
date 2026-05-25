<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplementSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'name',
        'type',
        'dosage_note',
        'timing_type',
        'scheduled_time',
        'meal_relation',
        'frequency',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'scheduled_time' => 'string',
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function logs()
    {
        return $this->hasMany(SupplementLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
