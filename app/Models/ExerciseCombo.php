<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciseCombo extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'target_skill',
        'estimated_minutes',
        'difficulty',
        'recommended_frequency',
        'parent_instructions',
    ];

    protected $casts = [
        'estimated_minutes' => 'integer',
    ];

    public function exercises()
    {
        return $this->belongsToMany(Exercise::class, 'exercise_combo_items', 'combo_id', 'exercise_id')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function items()
    {
        return $this->hasMany(ExerciseComboItem::class, 'combo_id')->orderBy('sort_order');
    }
}
