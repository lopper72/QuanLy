<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\SupplementLog;
use App\Models\SupplementSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SupplementController extends Controller
{
    public function index(): Response
    {
        $today = today()->toDateString();

        $schedules = SupplementSchedule::with(['child', 'logs' => fn ($query) => $query->whereDate('scheduled_for', $today)])
            ->orderBy('scheduled_time')
            ->orderBy('name')
            ->get();

        return Inertia::render('Supplements/Index', [
            'children' => Child::active()->orderBy('full_name')->get(['id', 'full_name', 'status']),
            'schedules' => $schedules,
            'todayReminders' => $schedules
                ->where('status', 'active')
                ->map(fn (SupplementSchedule $schedule) => $this->serializeReminder($schedule))
                ->values(),
            'safetyNote' => $this->safetyNote(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Supplements/Form', [
            'children' => Child::active()->orderBy('full_name')->get(['id', 'full_name', 'status']),
            'schedule' => null,
            'safetyNote' => $this->safetyNote(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        SupplementSchedule::create($this->validatedSchedule($request));

        return redirect()->route('supplements.index')->with('success', 'Đã tạo lịch bổ sung.');
    }

    public function edit(SupplementSchedule $supplement): Response
    {
        return Inertia::render('Supplements/Form', [
            'children' => Child::active()->orderBy('full_name')->get(['id', 'full_name', 'status']),
            'schedule' => $supplement->load('child'),
            'safetyNote' => $this->safetyNote(),
        ]);
    }

    public function update(Request $request, SupplementSchedule $supplement): RedirectResponse
    {
        $supplement->update($this->validatedSchedule($request));

        return redirect()->route('supplements.index')->with('success', 'Đã cập nhật lịch bổ sung.');
    }

    public function markTaken(Request $request, SupplementSchedule $supplement): RedirectResponse
    {
        $this->upsertLog($supplement, 'taken', $request->input('notes'));

        return back()->with('success', 'Đã ghi nhận đã uống.');
    }

    public function skip(Request $request, SupplementSchedule $supplement): RedirectResponse
    {
        $this->upsertLog($supplement, 'skipped', $request->input('notes'));

        return back()->with('success', 'Đã ghi nhận bỏ qua.');
    }

    private function validatedSchedule(Request $request): array
    {
        return $request->validate([
            'child_id' => ['required', Rule::exists('children', 'id')->where(fn ($query) => $query->where('status', 'active'))],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'dosage_note' => ['nullable', 'string'],
            'timing_type' => ['required', Rule::in(['fixed_time', 'before_meal', 'after_meal'])],
            'scheduled_time' => ['nullable', 'date_format:H:i'],
            'meal_relation' => ['nullable', 'string', 'max:100'],
            'frequency' => ['required', 'string', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', Rule::in(['active', 'paused', 'completed'])],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function upsertLog(SupplementSchedule $supplement, string $status, ?string $notes): SupplementLog
    {
        return SupplementLog::updateOrCreate(
            [
                'supplement_schedule_id' => $supplement->id,
                'scheduled_for' => today()->toDateString(),
            ],
            [
                'child_id' => $supplement->child_id,
                'status' => $status,
                'taken_at' => $status === 'taken' ? now() : null,
                'notes' => $notes,
            ]
        );
    }

    private function serializeReminder(SupplementSchedule $schedule): array
    {
        $log = $schedule->logs->first();

        return [
            'id' => $schedule->id,
            'child_name' => $schedule->child?->full_name,
            'name' => $schedule->name,
            'display_time' => $this->displayTime($schedule),
            'dosage_note' => $schedule->dosage_note,
            'status' => $log?->status ?? 'pending',
        ];
    }

    private function displayTime(SupplementSchedule $schedule): string
    {
        if ($schedule->timing_type === 'fixed_time' && $schedule->scheduled_time) {
            return substr($schedule->scheduled_time, 0, 5).' hằng ngày';
        }

        return match ($schedule->meal_relation ?: $schedule->timing_type) {
            'before_breakfast' => 'Trước bữa sáng',
            'before_lunch' => 'Trước bữa trưa',
            'before_dinner' => 'Trước bữa tối',
            'after_breakfast' => 'Sau bữa sáng',
            'after_lunch' => 'Sau bữa trưa',
            'after_dinner' => 'Sau bữa tối',
            'bedtime' => 'Trước khi ngủ',
            'before_meal' => 'Trước bữa ăn',
            'after_meal' => 'Sau bữa ăn',
            default => 'Theo lịch đã nhập',
        };
    }

    private function safetyNote(): string
    {
        return 'Thông tin này chỉ dùng để nhắc lịch. Liều dùng cần theo hướng dẫn của bác sĩ hoặc nhãn sản phẩm.';
    }
}
