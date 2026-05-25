<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Child;
use App\Http\Requests\Report\StoreReportRequest;
use App\Http\Requests\Report\UpdateReportRequest;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display a listing of reports.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['child_id', 'report_type', 'start_date', 'end_date']);
        $reports = $this->reportService->listReports($filters);
        $children = Child::orderBy('full_name')->get();

        return Inertia::render('Reports/Index', [
            'reports' => $reports,
            'children' => $children,
            'filters' => $filters,
            'reportTypes' => [
                'daily' => 'Hằng ngày',
                'weekly' => 'Hằng tuần',
                'monthly' => 'Hằng tháng',
                'custom' => 'Tùy chỉnh',
            ],
        ]);
    }

    /**
     * Show the form for creating a new report.
     */
    public function create(): Response
    {
        $children = Child::active()->orderBy('full_name')->get();

        return Inertia::render('Reports/Create', [
            'children' => $children,
            'reportTypes' => [
                'daily' => 'Hằng ngày',
                'weekly' => 'Hằng tuần',
                'monthly' => 'Hằng tháng',
                'custom' => 'Tùy chỉnh',
            ],
        ]);
    }

    /**
     * Store a newly created report.
     */
    public function store(StoreReportRequest $request): RedirectResponse
    {
        $report = $this->reportService->createReport($request->validated());

        return redirect()->route('reports.index')
            ->with('success', 'Đã tạo báo cáo.');
    }

    /**
     * Display the specified report.
     */
    public function show(Report $report): Response
    {
        $report = $this->reportService->getReportDetail($report);
        $summary = $this->reportService->buildReportSummary($report);

        return Inertia::render('Reports/Show', [
            'report' => $report,
            'summary' => $summary,
        ]);
    }

    /**
     * Show the form for editing the specified report.
     */
    public function edit(Report $report): Response
    {
        $children = Child::orderBy('full_name')->get();

        return Inertia::render('Reports/Edit', [
            'report' => $report,
            'children' => $children,
            'reportTypes' => [
                'daily' => 'Hằng ngày',
                'weekly' => 'Hằng tuần',
                'monthly' => 'Hằng tháng',
                'custom' => 'Tùy chỉnh',
            ],
        ]);
    }

    /**
     * Update the specified report.
     */
    public function update(Report $report, UpdateReportRequest $request): RedirectResponse
    {
        $this->reportService->updateReport($report, $request->validated());

        return redirect()->route('reports.show', $report->id)
            ->with('success', 'Đã cập nhật báo cáo.');
    }

    /**
     * Remove the specified report from storage.
     */
    public function destroy(Report $report): RedirectResponse
    {
        $this->reportService->deleteReport($report);

        return redirect()->route('reports.index')
            ->with('success', 'Đã xóa báo cáo.');
    }
}
