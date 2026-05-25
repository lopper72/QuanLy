<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyTrainingPlan extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'target_condition',
        'recommended_age',
    ];

    public function items()
    {
        return $this->hasMany(WeeklyTrainingPlanItem::class, 'weekly_plan_id')
            ->orderBy('day_of_week')
            ->orderBy('session_time');
    }
}
