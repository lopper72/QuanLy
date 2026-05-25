<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyMood extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'mood_date',
        'mood',
    ];

    protected $casts = [
        'mood_date' => 'date',
    ];
}
