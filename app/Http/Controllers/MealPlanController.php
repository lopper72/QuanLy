<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\MealLog;
use App\Models\MealPlanItem;
use App\Models\MealPlanTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MealPlanController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('MealPlans/Index', [
            'children' => Child::active()->orderBy('full_name')->get(['id', 'full_name', 'status']),
            'templates' => MealPlanTemplate::active()->with('items')->orderBy('week_number')->get(),
            'recentLogs' => MealLog::with(['child', 'item.template'])
                ->orderBy('meal_date', 'desc')
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get(),
            'safetyNote' => 'Nếu bé táo bón kéo dài, đau bụng nhiều, đi ngoài ra máu, nôn, sụt cân hoặc nhiều ngày không đi tiêu, cần liên hệ bác sĩ.',
            'supportNote' => 'Có thể hỗ trợ cải thiện thói quen đi tiêu nếu duy trì đều và phù hợp với bé.',
        ]);
    }

    public function apply(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'child_id' => ['required', Rule::exists('children', 'id')->where(fn ($query) => $query->where('status', 'active'))],
            'meal_plan_template_id' => ['required', 'exists:meal_plan_templates,id'],
        ]);

        $todayDay = (int) today()->dayOfWeekIso;
        $items = MealPlanItem::where('meal_plan_template_id', $validated['meal_plan_template_id'])
            ->where('day_of_week', $todayDay)
            ->get();

        foreach ($items as $item) {
            MealLog::firstOrCreate(
                [
                    'child_id' => $validated['child_id'],
                    'meal_plan_item_id' => $item->id,
                    'meal_date' => today()->toDateString(),
                ],
                ['status' => 'planned']
            );
        }

        return back()->with('success', 'Đã áp dụng thực đơn hôm nay cho bé.');
    }

    public function log(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'child_id' => ['required', Rule::exists('children', 'id')->where(fn ($query) => $query->where('status', 'active'))],
            'meal_plan_item_id' => ['nullable', 'exists:meal_plan_items,id'],
            'meal_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['planned', 'done', 'skipped'])],
            'scheduled_for' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'stool_note' => ['nullable', 'string'],
            'water_note' => ['nullable', 'string'],
        ]);

        MealLog::create([
            'child_id' => $validated['child_id'],
            'meal_plan_item_id' => $validated['meal_plan_item_id'] ?? null,
            'meal_date' => $validated['meal_date'] ?? today()->toDateString(),
            'scheduled_for' => $validated['scheduled_for'] ?? null,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'stool_note' => $validated['stool_note'] ?? null,
            'water_note' => $validated['water_note'] ?? null,
        ]);

        return back()->with('success', 'Đã ghi nhận bữa ăn hôm nay.');
    }
}
