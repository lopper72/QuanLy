<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MealLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'meal_plan_item_id',
        'meal_date',
        'scheduled_for',
        'status',
        'notes',
        'stool_note',
        'water_note',
    ];

    protected $casts = [
        'meal_date' => 'date',
        'scheduled_for' => 'datetime',
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function item()
    {
        return $this->belongsTo(MealPlanItem::class, 'meal_plan_item_id');
    }
}
