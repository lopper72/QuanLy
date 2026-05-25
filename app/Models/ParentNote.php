<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'checklist_item_id',
        'note',
        'noted_at',
    ];

    protected $casts = [
        'noted_at' => 'datetime',
    ];
}
