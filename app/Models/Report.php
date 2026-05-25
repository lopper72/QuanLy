<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'child_id',
        'report_type',
        'report_date',
        'summary',
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function scopeForChild($query, $childId)
    {
        return $query->where('child_id', $childId);
    }
}
