<?php

namespace App\Http\Controllers;

use App\Models\ChecklistItem;
use App\Models\Child;
use App\Services\DailyChecklistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DailyChecklistController extends Controller
{
    public function __construct(protected DailyChecklistService $dailyChecklistService)
    {
    }

    public function today(Request $request): Response
    {
        $request->validate([
            'context_mode' => ['nullable', 'string', 'in:home,supermarket,travel,grandparents,hospital'],
        ]);

        return Inertia::render('Checklist/Today', $this->dailyChecklistService->getTodayData(
            $request->input('context_mode')
        ));
    }

    public function updateItem(ChecklistItem $checklistItem, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:not_started,in_progress,completed,refused'],
            'performance_result' => ['nullable', 'string', 'in:good,needs_support,not_cooperative,hard_to_focus'],
            'parent_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->dailyChecklistService->updateItem($checklistItem, $data);

        return redirect()->back()->with('success', 'Đã cập nhật checklist.');
    }

    public function quickComplete(ChecklistItem $checklistItem): RedirectResponse
    {
        $this->dailyChecklistService->quickComplete($checklistItem);

        return redirect()->back()->with('success', 'Đã đánh dấu hoàn thành.');
    }

    public function carryOver(ChecklistItem $checklistItem): RedirectResponse
    {
        $this->dailyChecklistService->carryOver($checklistItem);

        return redirect()->back()->with('success', 'Đã chuyển bài tập sang ngày mai.');
    }

    public function mood(Child $child, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mood' => ['required', 'string', 'in:good,normal,tired,upset'],
        ]);

        $this->dailyChecklistService->updateMood($child, $data['mood']);

        return redirect()->back()->with('success', 'Đã lưu tâm trạng hôm nay.');
    }

    public function progressLog(Child $child, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $this->dailyChecklistService->addProgressLog($child, $data['title']);

        return redirect()->back()->with('success', 'Đã ghi nhận tiến bộ.');
    }
}
