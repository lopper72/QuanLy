<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Exercise;
use App\Models\TrainingSession;
use App\Models\TrainingSessionItem;
use App\Http\Requests\Training\StoreSessionRequest;
use App\Http\Requests\Training\UpdateSessionRequest;
use App\Services\TelegramTrainingNotificationService;
use App\Services\TrainingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrainingController extends Controller
{
    protected TrainingService $trainingService;

    public function __construct(TrainingService $trainingService)
    {
        $this->trainingService = $trainingService;
    }

    /**
     * Display a listing of training sessions grouped by child (timeline view).
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['child_id', 'status', 'date_from', 'date_to']);
        
        $sessions = $this->trainingService->listSessions($filters);
        $children = Child::orderBy('full_name')->get();
        
        // Group sessions by child for the timeline view
        $grouped = $this->trainingService->groupSessionsByChild($sessions);

        return Inertia::render('Training/Index', [
            'groupedSessions' => $grouped,
            'allChildren' => $children,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new training session.
     */
    public function create(): Response
    {
        $children = Child::active()->orderBy('full_name')->get();
        $exercises = Exercise::where('is_active', true)->orderBy('title')->get();

        return Inertia::render('Training/Create', [
            'children' => $children,
            'exercises' => $exercises,
        ]);
    }

    /**
     * Store a newly created training session.
     */
    public function store(StoreSessionRequest $request)
    {
        $session = $this->trainingService->createSession($request->validated());

        return redirect()
            ->route('training.show', $session->id)
            ->with('success', 'Đã tạo buổi tập.');
    }

    /**
     * Display the specified training session.
     */
    public function show(TrainingSession $trainingSession): Response
    {
        $session = $this->trainingService->getSessionDetail($trainingSession);

        return Inertia::render('Training/Show', [
            'session' => $session,
        ]);
    }

    /**
     * Show the form for editing the specified training session.
     */
    public function edit(TrainingSession $trainingSession): Response
    {
        $session = $this->trainingService->getSessionDetail($trainingSession);
        $children = Child::active()->orderBy('full_name')->get();
        $exercises = Exercise::where('is_active', true)->orderBy('title')->get();

        return Inertia::render('Training/Edit', [
            'session' => $session,
            'children' => $children,
            'exercises' => $exercises,
        ]);
    }

    /**
     * Update the specified training session in storage.
     */
    public function update(TrainingSession $trainingSession, UpdateSessionRequest $request)
    {
        $session = $this->trainingService->updateSession($trainingSession, $request->validated());

        return redirect()
            ->route('training.show', $session->id)
            ->with('success', 'Đã cập nhật buổi tập.');
    }

    /**
     * Remove the specified training session from storage.
     */
    public function destroy(TrainingSession $trainingSession)
    {
        $this->trainingService->deleteSession($trainingSession);

        return redirect()
            ->route('training.index')
            ->with('success', 'Đã xóa buổi tập.');
    }

    /**
     * Update the status of the training session.
     */
    public function updateStatus(TrainingSession $trainingSession, Request $request)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:planned,in_progress,completed,skipped,not_completed,need_help'],
        ]);

        $this->trainingService->updateSessionStatus($trainingSession, $request->input('status'));

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái buổi tập.');
    }

    /**
     * Update the status of a specific session item.
     */
    public function updateItemStatus(TrainingSessionItem $trainingSessionItem, Request $request)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:not_started,completed,partially_completed,skipped'],
        ]);

        $this->trainingService->updateItemStatus($trainingSessionItem, $request->input('status'));

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái bài tập.');
    }

    /**
     * Display today's training sessions checklist view.
     */
    public function today(): Response
    {
        $sessions = $this->trainingService->listTodaySessions();

        return Inertia::render('Training/Today', [
            'sessions' => $sessions,
        ]);
    }

    public function sendTelegram(TrainingSession $trainingSession, TelegramTrainingNotificationService $telegramTraining): \Illuminate\Http\RedirectResponse
    {
        try {
            $telegramTraining->sendTodayTraining($trainingSession->child);
        } catch (\InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Đã gửi lịch tập qua Telegram.');
    }

    public function sendTodayTelegram(TelegramTrainingNotificationService $telegramTraining): \Illuminate\Http\RedirectResponse
    {
        $children = Child::query()
            ->active()
            ->whereHas('trainingSessions', fn ($query) => $query->whereDate('session_date', today()))
            ->get();

        $sent = 0;
        foreach ($children as $child) {
            try {
                $telegramTraining->sendTodayTraining($child);
                $sent++;
            } catch (\InvalidArgumentException) {
                continue;
            }
        }

        if ($sent < 1) {
            return back()->with('error', 'Chưa có trẻ đủ điều kiện để gửi lịch hôm nay qua Telegram.');
        }

        return back()->with('success', "Đã gửi lịch hôm nay qua Telegram cho {$sent} trẻ.");
    }

    /**
     * Quick complete a specific training session item.
     */
    public function quickComplete(TrainingSessionItem $trainingSessionItem)
    {
        $this->trainingService->quickCompleteItem($trainingSessionItem);

        return redirect()->back()->with('success', 'Đã hoàn thành bài tập.');
    }

    /**
     * Quick skip a specific training session item.
     */
    public function quickSkip(TrainingSessionItem $trainingSessionItem)
    {
        $this->trainingService->quickSkipItem($trainingSessionItem);

        return redirect()->back()->with('success', 'Đã bỏ qua bài tập.');
    }

    /**
     * Quick update session notes (parent/therapist notes).
     */
    public function quickNote(TrainingSession $trainingSession, Request $request)
    {
        $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        $this->trainingService->updateSessionQuickNote($trainingSession, $request->input('notes'));

        return redirect()->back()->with('success', 'Đã cập nhật ghi chú buổi tập.');
    }

    /**
     * Delete all training sessions in an unknown group.
     */
    public function destroyUnknownGroup(string $groupKey)
    {
        $count = $this->trainingService->deleteUnknownGroup($groupKey);

        return redirect()
            ->route('training.index')
            ->with('success', "Đã xóa {$count} buổi tập không xác định.");
    }
}
