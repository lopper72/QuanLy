<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StreakTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'current_streak',
        'best_streak',
        'last_completed_date',
    ];

    protected $casts = [
        'last_completed_date' => 'date',
    ];
}
