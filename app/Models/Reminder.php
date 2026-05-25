<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'checklist_item_id',
        'remind_at',
        'channel',
        'status',
    ];

    protected $casts = [
        'remind_at' => 'datetime',
    ];
}
