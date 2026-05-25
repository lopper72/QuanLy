<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MealPlanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'meal_plan_template_id',
        'day_of_week',
        'meal_time',
        'scheduled_time',
        'title',
        'foods_json',
        'constipation_support_note',
        'parent_tip',
    ];

    protected $casts = [
        'foods_json' => 'array',
        'day_of_week' => 'integer',
        'scheduled_time' => 'string',
    ];

    public function template()
    {
        return $this->belongsTo(MealPlanTemplate::class, 'meal_plan_template_id');
    }

    public function logs()
    {
        return $this->hasMany(MealLog::class);
    }
}
