<?php

namespace App\Http\Controllers;

use App\Models\BehaviorLog;
use App\Models\Child;
use App\Models\TrainingSession;
use App\Models\TrainingSessionItem;
use App\Http\Requests\Behavior\StoreBehaviorRequest;
use App\Http\Requests\Behavior\UpdateBehaviorRequest;
use App\Services\BehaviorService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BehaviorController extends Controller
{
    protected BehaviorService $behaviorService;

    public function __construct(BehaviorService $behaviorService)
    {
        $this->behaviorService = $behaviorService;
    }

    /**
     * Show the quick behavior logging page.
     */
    public function quick(Request $request): Response
    {
        $children = $this->behaviorService->getActiveChildren();
        
        // Default to most recent child from behavior logs, or first child
        $defaultChildId = null;
        $latestLog = BehaviorLog::whereHas('child', fn ($query) => $query->active())
            ->latest('recorded_at')
            ->first();
        if ($latestLog) {
            $defaultChildId = $latestLog->child_id;
        } elseif ($children->isNotEmpty()) {
            $defaultChildId = $children->first()->id;
        }

        // Get selected child ID for filtering history / daily summary
        $selectedChildId = $request->input('child_id', $defaultChildId);

        // Fetch recent behavior logs (e.g., last 10 logs)
        $recentQuery = BehaviorLog::with('child')
            ->whereHas('child', fn ($query) => $query->active())
            ->orderBy('recorded_at', 'desc')
            ->limit(10);
        if ($selectedChildId) {
            $recentQuery->where('child_id', $selectedChildId);
        }
        $recentBehaviors = $recentQuery->get();

        // Calculate Daily behavior summary for today (local time)
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $todayQuery = BehaviorLog::whereHas('child', fn ($query) => $query->active())
            ->whereBetween('recorded_at', [$todayStart, $todayEnd]);
        if ($selectedChildId) {
            $todayQuery->where('child_id', $selectedChildId);
        }
        $todayLogs = $todayQuery->get();

        $dailySummary = [
            'total' => $todayLogs->count(),
            'low' => $todayLogs->where('severity', 'low')->count(),
            'medium' => $todayLogs->where('severity', 'medium')->count(),
            'high' => $todayLogs->where('severity', 'high')->count(),
        ];

        $presets = [
            ['key' => 'tantrum', 'label' => 'Ăn vạ', 'icon' => 'FlameIcon', 'color' => 'red'],
            ['key' => 'aggression', 'label' => 'Hành vi gây hấn', 'icon' => 'ShieldAlertIcon', 'color' => 'rose'],
            ['key' => 'sensory_seeking', 'label' => 'Tìm kiếm cảm giác', 'icon' => 'SparklesIcon', 'color' => 'indigo'],
            ['key' => 'avoidance', 'label' => 'Né tránh', 'icon' => 'UndoIcon', 'color' => 'amber'],
            ['key' => 'self_stimulation', 'label' => 'Tự kích thích', 'icon' => 'ActivityIcon', 'color' => 'purple'],
            ['key' => 'poor_sleep', 'label' => 'Ngủ kém', 'icon' => 'MoonIcon', 'color' => 'blue'],
            ['key' => 'picky_eating', 'label' => 'Kén ăn', 'icon' => 'UtensilsIcon', 'color' => 'emerald'],
            ['key' => 'transition_difficulty', 'label' => 'Khó chuyển hoạt động', 'icon' => 'RefreshCwIcon', 'color' => 'orange'],
        ];

        return Inertia::render('Behavior/Quick', [
            'children' => $children,
            'defaultChildId' => $defaultChildId,
            'selectedChildId' => $selectedChildId ? (int)$selectedChildId : null,
            'presets' => $presets,
            'severities' => [
                ['key' => 'low', 'label' => 'Nhẹ', 'color' => 'green'],
                ['key' => 'medium', 'label' => 'Trung bình', 'color' => 'yellow'],
                ['key' => 'high', 'label' => 'Cao', 'color' => 'red'],
            ],
            'recentBehaviors' => $recentBehaviors,
            'dailySummary' => $dailySummary,
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ]
        ]);
    }

    /**
     * Store a quick behavior log entry.
     */
    public function quickStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'child_id' => [
                'required',
                Rule::exists('children', 'id')->where(fn ($query) => $query
                    ->where('status', 'active')
                    ->whereNull('deleted_at')),
            ],
            'behavior_type' => [
                'required',
                'string',
                'max:100',
                Rule::in([
                    'tantrum',
                    'avoidance',
                    'sensory_seeking',
                    'aggression',
                    'self_stimulation',
                    'difficulty_transitioning',
                    'transition_difficulty',
                    'poor_sleep',
                    'picky_eating',
                    'other',
                ]),
            ],
            'severity' => [
                'nullable',
                'string',
                'max:50',
                Rule::in(['low', 'medium', 'high']),
            ],
            'trigger' => ['nullable', 'string'],
            'response' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
            'recorded_at' => ['nullable', 'date'],
        ]);

        if ($validated['behavior_type'] === 'transition_difficulty') {
            $validated['behavior_type'] = 'difficulty_transitioning';
        }

        if (empty($validated['recorded_at'])) {
            $validated['recorded_at'] = now()->format('Y-m-d H:i:s');
        }

        $this->behaviorService->createBehaviorLog($validated);

        return redirect()->back()->with('success', 'Đã ghi nhận hành vi.');
    }

    /**
     * Display a listing of behavior logs.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['child_id', 'behavior_type', 'severity', 'date_from', 'date_to', 'start_date', 'end_date']);
        
        $behaviorLogs = $this->behaviorService->listBehaviorLogs($filters);
        $behaviorGroups = $this->behaviorService->groupLogsByChild($behaviorLogs);
        $summary = $this->behaviorService->getBehaviorSummary($filters);
        $children = $this->behaviorService->getFilterChildren();
        $activeChildren = $this->behaviorService->getActiveChildren();

        return Inertia::render('Behavior/Index', [
            'behaviorLogs' => $behaviorLogs,
            'behaviorGroups' => $behaviorGroups,
            'summary' => $summary,
            'children' => $children,
            'activeChildren' => $activeChildren,
            'filters' => [
                'child_id' => $filters['child_id'] ?? '',
                'child_status' => Child::STATUS_ACTIVE,
                'behavior_type' => $filters['behavior_type'] ?? '',
                'severity' => $filters['severity'] ?? '',
                'date_from' => $filters['date_from'] ?? $filters['start_date'] ?? '',
                'date_to' => $filters['date_to'] ?? $filters['end_date'] ?? '',
            ],
            'behaviorTypes' => BehaviorService::BEHAVIOR_TYPES,
            'severities' => BehaviorService::SEVERITIES,
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ]
        ]);
    }

    /**
     * Show the form for creating a new behavior log.
     */
    public function create(Request $request): Response
    {
        $children = $this->behaviorService->getActiveChildren();
        $trainingSessions = TrainingSession::with(['child:id,full_name,status', 'items.exercise:id,title,category'])
            ->whereHas('child', fn ($query) => $query->active())
            ->orderByDesc('session_date')
            ->orderByDesc('scheduled_time')
            ->limit(100)
            ->get();

        $selectedTrainingSession = null;
        $selectedTrainingSessionItem = null;

        if ($request->filled('training_session_id')) {
            $selectedTrainingSession = TrainingSession::with(['child:id,full_name,status', 'items.exercise:id,title,category'])
                ->find($request->integer('training_session_id'));
        }

        if ($request->filled('training_session_item_id')) {
            $selectedTrainingSessionItem = TrainingSessionItem::with('exercise:id,title,category')
                ->find($request->integer('training_session_item_id'));
        }

        return Inertia::render('Behavior/Create', [
            'children' => $children,
            'trainingSessions' => $trainingSessions,
            'selectedTrainingSession' => $selectedTrainingSession,
            'selectedTrainingSessionItem' => $selectedTrainingSessionItem,
            'behaviorTypes' => BehaviorService::BEHAVIOR_TYPES,
            'severities' => BehaviorService::SEVERITIES,
        ]);
    }

    /**
     * Store a newly created behavior log in storage.
     */
    public function store(StoreBehaviorRequest $request): RedirectResponse
    {
        $this->behaviorService->createBehaviorLog($request->validated());

        return redirect()->route('behavior.index')
            ->with('success', 'Đã ghi nhận hành vi.');
    }

    /**
     * Display the specified behavior log.
     */
    public function show(BehaviorLog $behaviorLog): Response
    {
        $detailedLog = $this->behaviorService->getBehaviorLogDetail($behaviorLog);

        return Inertia::render('Behavior/Show', [
            'behaviorLog' => $detailedLog,
            'behaviorTypes' => BehaviorService::BEHAVIOR_TYPES,
            'severities' => BehaviorService::SEVERITIES,
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified behavior log.
     */
    public function edit(BehaviorLog $behaviorLog): Response
    {
        $children = Child::notVoided()
            ->orWhere('id', $behaviorLog->child_id)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'status']);

        return Inertia::render('Behavior/Edit', [
            'behaviorLog' => $behaviorLog,
            'children' => $children,
            'behaviorTypes' => BehaviorService::BEHAVIOR_TYPES,
            'severities' => BehaviorService::SEVERITIES,
        ]);
    }

    /**
     * Update the specified behavior log in storage.
     */
    public function update(UpdateBehaviorRequest $request, BehaviorLog $behaviorLog): RedirectResponse
    {
        $this->behaviorService->updateBehaviorLog($behaviorLog, $request->validated());

        return redirect()->route('behavior.show', $behaviorLog->id)
            ->with('success', 'Đã cập nhật hành vi.');
    }

    /**
     * Remove the specified behavior log from storage.
     */
    public function destroy(BehaviorLog $behaviorLog): RedirectResponse
    {
        $this->behaviorService->deleteBehaviorLog($behaviorLog);

        return redirect()->route('behavior.index')
            ->with('success', 'Đã xóa ghi nhận hành vi.');
    }
}
