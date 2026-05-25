<?php

namespace App\Services;

use App\Models\Intervention;

class InterventionService
{
    public function listInterventions()
    {
        return Intervention::query()->with('child')->latest()->get();
    }

    public function createIntervention(array $data): Intervention
    {
        return Intervention::create($data);
    }

    public function updateIntervention($id, array $data): Intervention
    {
        $intervention = Intervention::findOrFail($id);
        $intervention->update($data);
        return $intervention;
    }
}
