<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplementLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplement_schedule_id',
        'child_id',
        'scheduled_for',
        'taken_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_for' => 'date',
        'taken_at' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(SupplementSchedule::class, 'supplement_schedule_id');
    }

    public function child()
    {
        return $this->belongsTo(Child::class);
    }
}
