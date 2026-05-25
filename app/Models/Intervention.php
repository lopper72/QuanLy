<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'plan_name',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }
}
