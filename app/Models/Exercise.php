<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exercise extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'difficulty',
        'instructions',
        'description',
        'target_skill',
        'recommended_age',
        'required_tools',
        'expected_benefits',
        'safety_notes',
        'parent_tips',
        'weekly_expectation',
        'thumbnail_path',
        'video_path',
        'video_url',
        'estimated_minutes',
        'is_active',
    ];

    protected $casts = [
        'estimated_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    public function trainingSessionItems()
    {
        return $this->hasMany(TrainingSessionItem::class);
    }

    public function steps()
    {
        return $this->hasMany(ExerciseStep::class)->orderBy('sort_order');
    }

    public function combos()
    {
        return $this->belongsToMany(ExerciseCombo::class, 'exercise_combo_items', 'exercise_id', 'combo_id')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
