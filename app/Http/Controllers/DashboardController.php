<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the system dashboard.
     */
    public function index(): Response
    {
        $data = $this->dashboardService->getDashboardData();

        return Inertia::render('Dashboard/Index', $data);
    }
}