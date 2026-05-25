<?php

namespace App\Services;

use App\Models\Child;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class ChildService
{
    /**
     * List all children with optional search filter.
     */
    public function listChildren(?string $search = null, ?string $status = null): Collection
    {
        $query = Child::query();

        if ($status === Child::STATUS_VOIDED) {
            $query->voided();
        } elseif ($status !== 'all') {
            $query->notVoided();
        }

        if ($status === Child::STATUS_ACTIVE) {
            $query->active();
        }

        if ($status === Child::STATUS_PAUSED) {
            $query->paused();
        }

        if ($status === Child::STATUS_STOPPED) {
            $query->stopped();
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nickname', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('full_name')->get();
    }

    /**
     * Create a new child.
     */
    public function createChild(array $data): Child
    {
        $data['status'] = $data['status'] ?? Child::STATUS_ACTIVE;

        return Child::create($data);
    }

    /**
     * Get detailed child information, including relationships.
     */
    public function getChildDetail(Child $child): Child
    {
        return $child->load([
            'trainingSessions' => function ($query) {
                $query->orderBy('session_date', 'desc')
                    ->orderBy('scheduled_time', 'desc')
                    ->orderBy('id', 'desc')
                    ->with('items.exercise')
                    ->limit(5);
            },
            'assessments' => function ($q) {
                $q->latest()->limit(5);
            },
            'behaviorLogs' => function ($q) {
                $q->latest()->limit(5);
            },
            'reports' => function ($q) {
                $q->latest()->limit(5);
            }
        ]);
    }

    /**
     * Update an existing child.
     */
    public function updateChild(Child $child, array $data): Child
    {
        $child->update($data);
        return $child;
    }

    /**
     * Delete (soft delete) an existing child.
     * Only allowed for voided or stopped children.
     */
    public function deleteChild(Child $child): bool
    {
        if (!in_array($child->status, [Child::STATUS_VOIDED, Child::STATUS_STOPPED], true)) {
            return false;
        }

        $child->delete();

        return true;
    }

    public function pauseChild(Child $child, ?string $note = null): Child
    {
        if (!in_array($child->status, [Child::STATUS_ACTIVE], true)) {
            return $child;
        }

        $child->update([
            'status' => Child::STATUS_PAUSED,
            'paused_at' => Carbon::now(),
            'voided_at' => null,
            'status_note' => $note,
        ]);

        return $child;
    }

    public function activateChild(Child $child): Child
    {
        $this->resumeChild($child);

        return $child->refresh();
    }

    public function resumeChild(Child $child): bool
    {
        if ($child->trashed() || !in_array($child->status, [Child::STATUS_PAUSED, Child::STATUS_STOPPED], true)) {
            return false;
        }

        $data = [
            'status' => Child::STATUS_ACTIVE,
            'paused_at' => null,
            'voided_at' => null,
            'status_note' => null,
        ];

        if (Schema::hasColumn($child->getTable(), 'resumed_at')) {
            $data['resumed_at'] = Carbon::now();
        }

        $child->forceFill($data)->save();

        return true;
    }

    public function voidChild(Child $child, ?string $note = null): Child
    {
        if (!in_array($child->status, [Child::STATUS_ACTIVE, Child::STATUS_PAUSED, Child::STATUS_STOPPED], true)) {
            return $child;
        }

        $child->update([
            'status' => Child::STATUS_VOIDED,
            'voided_at' => Carbon::now(),
            'status_note' => $note,
        ]);

        return $child;
    }
}
