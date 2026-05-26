<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchedulerRun extends Model
{
    use HasFactory;

    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'command',
        'ran_at',
        'status',
        'output_summary',
        'error_message',
    ];

    protected $casts = [
        'ran_at' => 'datetime',
    ];
}
