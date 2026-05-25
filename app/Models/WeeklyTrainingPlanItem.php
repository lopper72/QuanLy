<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyTrainingPlanItem extends Model
{
    protected $fillable = [
        'weekly_plan_id',
        'exercise_id',
        'combo_id',
        'day_of_week',
        'session_time',
        'estimated_minutes',
        'notes',
    ];

    protected $casts = [
        'estimated_minutes' => 'integer',
    ];

    public function weeklyPlan()
    {
        return $this->belongsTo(WeeklyTrainingPlan::class, 'weekly_plan_id');
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }

    public function combo()
    {
        return $this->belongsTo(ExerciseCombo::class, 'combo_id');
    }
}
