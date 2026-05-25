<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInterventionRequest;
use App\Http\Requests\UpdateInterventionRequest;
use App\Services\InterventionService;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ChildInterventionController extends Controller
{
    public function index(InterventionService $service)
    {
        $interventions = $service->listInterventions();

        return Inertia::render('Interventions/Index', [
            'interventions' => $interventions,
        ]);
    }

    public function create()
    {
        return Inertia::render('Interventions/Create');
    }

    public function store(StoreInterventionRequest $request, InterventionService $service)
    {
        $service->createIntervention($request->validated());

        return redirect()->route('interventions.index');
    }

    public function edit($id)
    {
        return Inertia::render('Interventions/Edit', [
            'interventionId' => $id,
        ]);
    }

    public function update(UpdateInterventionRequest $request, $id, InterventionService $service)
    {
        $service->updateIntervention($id, $request->validated());

        return redirect()->route('interventions.index');
    }
}
