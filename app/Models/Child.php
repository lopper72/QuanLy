<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Child extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_STOPPED = 'stopped';
    public const STATUS_VOIDED = 'voided';

    protected $fillable = [
        'full_name',
        'nickname',
        'date_of_birth',
        'gender',
        'diagnosis_level',
        'notes',
        'status',
        'paused_at',
        'voided_at',
        'status_note',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'paused_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    protected $appends = [
        'first_name',
        'last_name',
    ];

    public function getFirstNameAttribute()
    {
        if (empty($this->full_name)) {
            return '';
        }
        $parts = explode(' ', $this->full_name);
        return $parts[0] ?? '';
    }

    public function getLastNameAttribute()
    {
        if (empty($this->full_name)) {
            return '';
        }
        $parts = explode(' ', $this->full_name);
        if (count($parts) > 1) {
            array_shift($parts);
            return implode(' ', $parts);
        }
        return '';
    }

    public function trainingSessions()
    {
        return $this->hasMany(TrainingSession::class);
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function behaviorLogs()
    {
        return $this->hasMany(BehaviorLog::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function dailyChecklists()
    {
        return $this->hasMany(DailyChecklist::class);
    }

    public function dailyMoods()
    {
        return $this->hasMany(DailyMood::class);
    }

    public function progressLogs()
    {
        return $this->hasMany(ProgressLog::class);
    }

    public function supplementSchedules()
    {
        return $this->hasMany(SupplementSchedule::class);
    }

    public function supplementLogs()
    {
        return $this->hasMany(SupplementLog::class);
    }

    public function mealLogs()
    {
        return $this->hasMany(MealLog::class);
    }

    public function streakTracking()
    {
        return $this->hasOne(StreakTracking::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isPaused(): bool
    {
        return $this->status === self::STATUS_PAUSED;
    }

    public function isStopped(): bool
    {
        return $this->status === self::STATUS_STOPPED;
    }

    public function isVoided(): bool
    {
        return $this->status === self::STATUS_VOIDED;
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereNull($this->getQualifiedDeletedAtColumn());
    }

    public function scopeActiveForWorkflow($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereNull($this->getQualifiedDeletedAtColumn());
    }

    public function scopePaused($query)
    {
        return $query->where('status', self::STATUS_PAUSED)
            ->whereNull($this->getQualifiedDeletedAtColumn());
    }

    public function scopeStopped($query)
    {
        return $query->where('status', self::STATUS_STOPPED);
    }

    public function scopeResumable($query)
    {
        return $query->whereIn('status', [self::STATUS_PAUSED, self::STATUS_STOPPED]);
    }

    public function scopeVoided($query)
    {
        return $query->where('status', self::STATUS_VOIDED)
            ->whereNull($this->getQualifiedDeletedAtColumn());
    }

    public function scopeNotVoided($query)
    {
        return $query->where('status', '!=', self::STATUS_VOIDED)
            ->whereNull($this->getQualifiedDeletedAtColumn());
    }
}
