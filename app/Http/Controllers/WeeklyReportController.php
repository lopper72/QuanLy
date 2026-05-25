<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Services\WeeklyReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class WeeklyReportController extends Controller
{
    protected $weeklyReportService;

    public function __construct(WeeklyReportService $weeklyReportService)
    {
        $this->weeklyReportService = $weeklyReportService;
    }

    /**
     * Show the weekly report generation page.
     */
    public function index(Request $request)
    {
        return Inertia::render('Reports/Weekly', [
            'children' => Child::orderBy('full_name')->get(['id', 'full_name', 'status']),
            'filters' => $request->only(['child_id', 'start_date', 'end_date']),
        ]);
    }

    /**
     * Generate the report data and show preview.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'child_id' => 'required|exists:children,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $reportData = $this->weeklyReportService->getWeeklyReportData(
            $request->child_id,
            $request->start_date,
            $request->end_date
        );

        return Inertia::render('Reports/Weekly', [
            'children' => Child::orderBy('full_name')->get(['id', 'full_name', 'status']),
            'reportData' => $reportData,
            'filters' => $request->only(['child_id', 'start_date', 'end_date']),
        ]);
    }

    /**
     * Download the weekly report as PDF.
     */
    public function download(Request $request, Child $child)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $reportData = $this->weeklyReportService->getWeeklyReportData(
            $child->id,
            $request->start_date,
            $request->end_date
        );

        $pdf = Pdf::loadView('reports.weekly-pdf', $reportData);
        
        $filename = sprintf(
            'Weekly_Report_%s_%s_to_%s.pdf',
            $child->first_name,
            $request->start_date,
            $request->end_date
        );

        return $pdf->download($filename);
    }
}
