<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciseComboItem extends Model
{
    protected $fillable = [
        'combo_id',
        'exercise_id',
        'sort_order',
    ];

    public function combo()
    {
        return $this->belongsTo(ExerciseCombo::class, 'combo_id');
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }
}
