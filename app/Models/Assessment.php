<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assessment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'child_id',
        'assessment_date',
        'overall_score',
        'notes',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'overall_score' => 'integer',
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function items()
    {
        return $this->hasMany(AssessmentItem::class);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('assessment_date', 'desc');
    }
}
