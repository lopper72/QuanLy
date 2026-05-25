<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'checklist_date',
        'context_mode',
        'summary',
    ];

    protected $casts = [
        'checklist_date' => 'date',
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function items()
    {
        return $this->hasMany(ChecklistItem::class);
    }

    public function progress()
    {
        return $this->hasOne(ChecklistProgress::class);
    }
}
