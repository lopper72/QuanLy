<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MealPlanTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'goal',
        'description',
        'week_number',
        'is_active',
    ];

    protected $casts = [
        'week_number' => 'integer',
        'is_active' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(MealPlanItem::class)->orderBy('day_of_week')->orderBy('meal_time');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
