<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExerciseStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'exercise_id',
        'title',
        'instruction',
        'image_path',
        'sort_order',
    ];

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }
}