<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'skill_name',
        'score',
        'level',
        'note',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}
