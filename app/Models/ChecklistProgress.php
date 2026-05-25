<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_checklist_id',
        'total_items',
        'completed_items',
        'remaining_items',
        'completion_percent',
    ];

    public function dailyChecklist()
    {
        return $this->belongsTo(DailyChecklist::class);
    }
}
