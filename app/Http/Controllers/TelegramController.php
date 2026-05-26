<?php

namespace App\Http\Controllers;

use App\Models\TelegramContact;
use App\Models\TelegramMessage;
use App\Models\TelegramMealSuggestionLog;
use App\Models\TelegramReminderLog;
use App\Models\TelegramSetting;
use App\Models\Child;
use App\Models\Exercise;
use App\Models\MealPlanItem;
use App\Models\MealPlanTemplate;
use App\Models\SchedulerRun;
use App\Models\SupplementSchedule;
use App\Models\TrainingSession;
use App\Models\TrainingSessionItem;
use App\Services\TelegramService;
use App\Services\TelegramCommandService;
use App\Services\TelegramMealSuggestionService;
use App\Services\TelegramReminderService;
use App\Services\TelegramTrainingNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Inertia\Response;

class TelegramController extends Controller
{
    public function index(Request $request): Response
    {
        $selectedChatId = $request->string('chat_id')->toString()
            ?: TelegramContact::query()->latest('last_seen_at')->value('telegram_chat_id');

        return Inertia::render('Telegram/Index', [
            'contacts' => $this->contactsPayload(),
            'messages' => $this->messagesPayload($selectedChatId),
            'selectedChatId' => $selectedChatId,
            'settings' => $this->settingsPayload(),
            'stats' => $this->statsPayload(),
            'trainingTest' => $this->trainingTestPayload(),
            'systemStatus' => $this->systemStatusPayload(),
            'schedulerDiagnostics' => $this->schedulerDiagnosticsPayload(),
            'reminderTest' => $this->reminderTestPayload(),
            'mealSuggestionTest' => $this->mealSuggestionTestPayload(),
        ]);
    }

    public function settings(): Response
    {
        return Inertia::render('Telegram/Settings', [
            'settings' => $this->settingsPayload(),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bot_token' => ['nullable', 'string', 'max:500'],
            'bot_username' => ['nullable', 'string', 'max:255'],
            'webhook_secret' => ['nullable', 'string', 'max:255'],
            'webhook_url' => ['nullable', 'url', 'max:500'],
            'default_chat_id' => ['nullable', 'string', 'max:255'],
            'enabled' => ['boolean'],
        ]);

        $settings = TelegramSetting::current();
        $settings->fill([
            'bot_username' => $validated['bot_username'] ?? null,
            'webhook_url' => $validated['webhook_url'] ?? null,
            'default_chat_id' => $validated['default_chat_id'] ?? null,
            'enabled' => (bool) ($validated['enabled'] ?? false),
        ]);

        if (filled($validated['bot_token'] ?? null)) {
            $settings->bot_token = $validated['bot_token'];
        }

        if (filled($validated['webhook_secret'] ?? null)) {
            $settings->webhook_secret = $validated['webhook_secret'];
        }

        $settings->save();

        return back()->with('success', 'Đã lưu cấu hình Telegram.');
    }

    public function registerWebhook(TelegramService $telegramService): RedirectResponse
    {
        $response = $telegramService->setWebhook();

        if (!$response?->successful()) {
            return back()->with('error', 'Chưa đăng ký được webhook Telegram. Vui lòng kiểm tra cấu hình bot.');
        }

        return back()->with('success', 'Đã đăng ký webhook Telegram.');
    }

    public function deleteWebhook(TelegramService $telegramService): RedirectResponse
    {
        $response = $telegramService->deleteWebhook();

        if (!$response?->successful()) {
            return back()->with('error', 'Chưa xóa được webhook Telegram.');
        }

        return back()->with('success', 'Đã xóa webhook Telegram.');
    }

    public function testSend(Request $request, TelegramService $telegramService): RedirectResponse
    {
        $validated = $request->validate([
            'chat_id' => ['required', 'string', 'max:255'],
            'message_text' => ['required', 'string', 'max:4000'],
        ]);

        $telegramService->sendTestMessage($validated['chat_id'], $validated['message_text']);

        return back()->with('success', 'Đã gửi tin nhắn thử nghiệm.');
    }

    public function messages(Request $request): JsonResponse
    {
        $selectedChatId = $request->string('chat_id')->toString();

        return response()->json([
            'contacts' => $this->contactsPayload(),
            'messages' => $this->messagesPayload($selectedChatId),
            'stats' => $this->statsPayload(),
        ]);
    }

    public function webhookInfo(TelegramService $telegramService): JsonResponse
    {
        $response = $telegramService->getWebhookInfo();

        if (!$response) {
            return response()->json([
                'ok' => false,
                'message' => 'Chưa cấu hình bot Telegram.',
            ], 422);
        }

        return response()->json([
            'ok' => $response->successful(),
            'status' => $response->status(),
            'telegram' => $response->json(),
        ]);
    }

    public function testBot(TelegramService $telegramService): RedirectResponse
    {
        $response = $telegramService->getWebhookInfo();

        if (!$response?->successful()) {
            return back()->with('error', 'Chưa cấu hình Telegram Bot.');
        }

        return back()->with('success', 'Bot Telegram đã phản hồi.');
    }

    public function testWebhookInfo(TelegramService $telegramService): RedirectResponse
    {
        $response = $telegramService->getWebhookInfo();

        if (!$response?->successful()) {
            return back()->with('error', 'Chưa xem được trạng thái webhook.');
        }

        return back()->with('success', 'Đã kiểm tra trạng thái webhook.');
    }

    public function testSendMessage(Request $request, TelegramService $telegramService): RedirectResponse
    {
        return $this->testSend($request, $telegramService);
    }

    public function testTrainingSchedule(Request $request, TelegramTrainingNotificationService $service): RedirectResponse
    {
        return $this->sendTodayTraining($request, $service);
    }

    public function testReminderTraining(TelegramReminderService $service): RedirectResponse
    {
        return $this->sendTestReminder($service, TelegramReminderLog::TYPE_TRAINING);
    }

    public function testReminderMeal(Request $request, TelegramMealSuggestionService $service): RedirectResponse
    {
        return $this->testDinnerSuggestion($request, $service);
    }

    public function testReminderSupplement(TelegramReminderService $service): RedirectResponse
    {
        return $this->sendTestReminder($service, TelegramReminderLog::TYPE_SUPPLEMENT);
    }

    public function simulateCallback(Request $request, TelegramTrainingNotificationService $trainingService): RedirectResponse
    {
        return $this->simulateTrainingCallback($request, $trainingService);
    }

    public function testDinnerSuggestion(Request $request, TelegramMealSuggestionService $service): RedirectResponse
    {
        $child = $this->validatedMealSuggestionChild($request);
        $log = $service->sendDinnerSuggestionForChild($child, today());

        if (!$log) {
            return back()->with('error', 'Chưa có mã hội thoại Telegram cho phụ huynh.');
        }

        return back()->with('success', 'Đã gửi thử gợi ý bữa tối lúc 14:00.');
    }

    public function testMealCommandAn(Request $request, TelegramMealSuggestionService $service): RedirectResponse
    {
        $child = $this->validatedMealSuggestionChild($request);
        $chatId = $service->resolveChatId($child);
        if (blank($chatId)) {
            return back()->with('error', 'Chưa có mã hội thoại Telegram cho phụ huynh.');
        }

        TelegramMessage::create([
            'direction' => TelegramMessage::DIRECTION_INBOUND,
            'telegram_chat_id' => $chatId,
            'message_type' => 'text',
            'message_text' => '/an',
            'payload_json' => ['source' => 'telegram_test_center'],
            'delivery_status' => TelegramMessage::STATUS_RECEIVED,
            'received_at' => now(),
            'related_child_id' => $child->id,
        ]);
        $service->sendTodayMealScheduleForChat($chatId, $child, today());

        return back()->with('success', 'Đã giả lập lệnh /an.');
    }

    public function testMealCommandDoimon(Request $request, TelegramMealSuggestionService $service): RedirectResponse
    {
        $child = $this->validatedMealSuggestionChild($request);
        $chatId = $service->resolveChatId($child);
        if (blank($chatId)) {
            return back()->with('error', 'Chưa có mã hội thoại Telegram cho phụ huynh.');
        }

        TelegramMessage::create([
            'direction' => TelegramMessage::DIRECTION_INBOUND,
            'telegram_chat_id' => $chatId,
            'message_type' => 'text',
            'message_text' => '/doimon',
            'payload_json' => ['source' => 'telegram_test_center'],
            'delivery_status' => TelegramMessage::STATUS_RECEIVED,
            'received_at' => now(),
            'related_child_id' => $child->id,
        ]);
        $service->sendAlternativeDinnerForChat($chatId, $child, today());

        return back()->with('success', 'Đã giả lập lệnh /doimon.');
    }

    public function testMealSuggestionCallback(Request $request, TelegramMealSuggestionService $service): RedirectResponse
    {
        $validated = $request->validate([
            'child_id' => ['required', 'integer', 'exists:children,id'],
            'action' => ['required', 'string', 'in:change,view,prepared'],
        ]);
        $child = Child::active()->findOrFail($validated['child_id']);
        $chatId = $service->resolveChatId($child) ?: 'test-chat';

        $service->handleCallback([
            'id' => 'simulate-meal-'.now()->timestamp,
            'data' => "meal_suggestion:{$child->id}:".today()->toDateString().":{$validated['action']}",
            'from' => ['id' => 'simulate-user', 'first_name' => 'Phụ huynh'],
            'message' => [
                'date' => now()->timestamp,
                'chat' => ['id' => $chatId, 'first_name' => 'Phụ huynh'],
            ],
        ]);

        return back()->with('success', 'Đã giả lập phản hồi gợi ý bữa tối.');
    }

    public function createTodayDemoData(TelegramReminderService $service): RedirectResponse
    {
        $child = Child::active()->orderBy('id')->first();
        $chatId = TelegramSetting::current()->default_chat_id
            ?: \App\Models\User::query()->whereNotNull('telegram_chat_id')->where('telegram_notifications_enabled', true)->value('telegram_chat_id');

        if (!$child || blank($chatId)) {
            return back()->with('error', 'Chưa có mã hội thoại Telegram. Vui lòng liên kết Telegram hoặc nhập chat_id để test.');
        }

        $time = now()->addMinutes(30)->format('H:i');
        $exercise = Exercise::firstOrCreate(
            ['slug' => 'demo-telegram-van-dong-nhe'],
            [
                'title' => 'Vận động nhẹ demo',
                'category' => 'gross_motor',
                'difficulty' => 'easy',
                'instructions' => 'Tập nhẹ theo hướng dẫn của phụ huynh.',
                'description' => 'Bài tập demo để kiểm tra nhắc lịch Telegram.',
                'estimated_minutes' => 10,
                'is_active' => true,
            ]
        );

        $session = TrainingSession::firstOrCreate(
            ['child_id' => $child->id, 'session_date' => today()->toDateString(), 'scheduled_time' => $time],
            ['status' => 'pending', 'total_minutes' => 10, 'notes' => 'Dữ liệu demo Telegram']
        );
        TrainingSessionItem::firstOrCreate(
            ['training_session_id' => $session->id, 'exercise_id' => $exercise->id],
            ['sort_order' => 1, 'duration_minutes' => 10, 'completion_status' => 'pending']
        );

        $supplement = SupplementSchedule::firstOrCreate(
            ['child_id' => $child->id, 'name' => 'DHA demo'],
            [
                'type' => 'supplement',
                'dosage_note' => 'Theo hướng dẫn của bác sĩ hoặc nhãn sản phẩm.',
                'timing_type' => 'fixed_time',
                'scheduled_time' => $time,
                'frequency' => 'daily',
                'status' => 'active',
            ]
        );

        $template = MealPlanTemplate::firstOrCreate(
            ['title' => 'Demo lịch ăn Telegram'],
            [
                'goal' => 'constipation_support',
                'description' => 'Dữ liệu demo để kiểm tra nhắc lịch ăn uống.',
                'week_number' => 1,
                'is_active' => true,
            ]
        );
        $meal = MealPlanItem::firstOrCreate(
            ['meal_plan_template_id' => $template->id, 'day_of_week' => today()->dayOfWeekIso, 'meal_time' => 'dinner'],
            [
                'scheduled_time' => $time,
                'title' => 'Bữa tối demo',
                'foods_json' => ['Cơm mềm', 'Canh bí đỏ', 'Cá hấp', 'Thanh long chín'],
                'constipation_support_note' => 'Có thể hỗ trợ tiêu hóa nếu phù hợp với bé.',
                'parent_tip' => 'Cho bé thử từng lượng nhỏ.',
            ]
        );

        $service->createTrainingReminder($session->refresh());
        $service->createSupplementReminder($supplement->refresh());
        $service->createMealReminder($meal->refresh());

        return back()->with('success', "Đã tạo dữ liệu demo cho hôm nay. Các lịch demo được đặt lúc {$time} để nhắc ngay.");
    }

    public function sendDinnerNow(Request $request, TelegramMealSuggestionService $service): RedirectResponse
    {
        return $this->testDinnerSuggestion($request, $service);
    }

    public function sendTrainingReminderNow(TelegramReminderService $service): RedirectResponse
    {
        return $this->sendTestReminder($service, TelegramReminderLog::TYPE_TRAINING);
    }

    public function sendMealReminderNow(Request $request, TelegramMealSuggestionService $service): RedirectResponse
    {
        return $this->testDinnerSuggestion($request, $service);
    }

    public function sendSupplementReminderNow(TelegramReminderService $service): RedirectResponse
    {
        return $this->sendTestReminder($service, TelegramReminderLog::TYPE_SUPPLEMENT);
    }

    public function simulateAnCommand(Request $request, TelegramMealSuggestionService $service): RedirectResponse
    {
        return $this->testMealCommandAn($request, $service);
    }

    public function simulateDoimonCommand(Request $request, TelegramMealSuggestionService $service): RedirectResponse
    {
        return $this->testMealCommandDoimon($request, $service);
    }

    public function simulateQuickCommand(Request $request, TelegramCommandService $service): RedirectResponse
    {
        $validated = $request->validate([
            'command' => ['required', 'string', 'in:/menu,/tap,/thuoc,/tiendo,/ditoilet,/uongnuoc'],
        ]);

        $chatId = TelegramSetting::current()->default_chat_id
            ?: TelegramContact::query()->latest('last_seen_at')->value('telegram_chat_id');

        if (blank($chatId)) {
            return back()->with('error', 'Chưa có mã hội thoại Telegram để test.');
        }

        TelegramMessage::create([
            'direction' => TelegramMessage::DIRECTION_INBOUND,
            'telegram_chat_id' => (string) $chatId,
            'message_type' => 'text',
            'message_text' => $validated['command'],
            'payload_json' => ['source' => 'telegram_test_center'],
            'delivery_status' => TelegramMessage::STATUS_RECEIVED,
            'received_at' => now(),
        ]);

        $service->handleMessage([
            'text' => $validated['command'],
            'chat' => ['id' => $chatId],
            'from' => ['id' => $chatId, 'first_name' => 'Phụ huynh'],
        ]);

        return back()->with('success', "Đã giả lập lệnh {$validated['command']}.");
    }

    public function runDinnerCommand(): RedirectResponse
    {
        $exitCode = Artisan::call('telegram:send-dinner-suggestions');

        return back()->with($exitCode === 0 ? 'success' : 'error', trim(Artisan::output()) ?: 'Đã chạy thử lệnh gợi ý bữa tối.');
    }

    public function runReminderCommand(): RedirectResponse
    {
        $exitCode = Artisan::call('telegram:send-due-reminders');

        return back()->with($exitCode === 0 ? 'success' : 'error', trim(Artisan::output()) ?: 'Đã chạy thử lệnh nhắc lịch.');
    }

    private function sendTestReminder(TelegramReminderService $service, string $type): RedirectResponse
    {
        try {
            $log = $service->testReminder($type);
            $service->sendReminderLog($log);
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Đã gửi thử nhắc lịch Telegram.');
    }

    private function validatedMealSuggestionChild(Request $request): Child
    {
        $validated = $request->validate([
            'child_id' => ['required', 'integer', 'exists:children,id'],
        ]);

        return Child::active()->findOrFail($validated['child_id']);
    }

    public function health(TelegramService $telegramService): JsonResponse
    {
        $info = $telegramService->getWebhookInfo();
        $lastInbound = TelegramMessage::query()->where('direction', TelegramMessage::DIRECTION_INBOUND)->latest('received_at')->first();
        $lastOutbound = TelegramMessage::query()->where('direction', TelegramMessage::DIRECTION_OUTBOUND)->latest('sent_at')->first();

        return response()->json([
            'webhook_configured' => filled($telegramService->webhookUrl()),
            'bot_reachable' => (bool) $info?->successful(),
            'queue' => [
                'connection' => config('queue.default'),
            ],
            'last_inbound_at' => optional($lastInbound?->received_at)->toIso8601String(),
            'last_outbound_at' => optional($lastOutbound?->sent_at)->toIso8601String(),
        ]);
    }

    public function send(Request $request, TelegramService $telegramService): RedirectResponse
    {
        $validated = $request->validate([
            'chat_id' => ['required', 'string', 'max:255'],
            'message_text' => ['required', 'string', 'max:4000'],
        ]);

        $telegramService->sendMessage($validated['chat_id'], $validated['message_text']);

        return back()->with('success', 'Đã gửi tin nhắn.');
    }

    public function sendTodayTraining(Request $request, TelegramTrainingNotificationService $service): RedirectResponse
    {
        $validated = $request->validate([
            'child_id' => ['required', 'integer', 'exists:children,id'],
        ]);

        try {
            $service->sendTodayTraining(Child::findOrFail($validated['child_id']));
        } catch (\InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Đã gửi lịch tập hôm nay qua Telegram.');
    }

    public function simulateTrainingCallback(Request $request, TelegramTrainingNotificationService $service): RedirectResponse
    {
        $validated = $request->validate([
            'training_session_id' => ['required', 'integer', 'exists:training_sessions,id'],
            'action' => ['required', 'string', 'in:completed,not_completed,skipped,need_help'],
        ]);

        $service->simulateCallback(
            TrainingSession::findOrFail($validated['training_session_id']),
            $validated['action']
        );

        return back()->with('success', 'Đã giả lập callback Telegram.');
    }

    private function contactsPayload()
    {
        return TelegramContact::query()
            ->withCount('messages')
            ->latest('last_seen_at')
            ->get()
            ->map(fn (TelegramContact $contact) => [
                'telegram_chat_id' => $contact->telegram_chat_id,
                'telegram_user_id' => $contact->telegram_user_id,
                'telegram_username' => $contact->telegram_username,
                'display_name' => $contact->display_name,
                'last_seen_at' => optional($contact->last_seen_at)->toIso8601String(),
                'is_active' => $contact->is_active,
                'messages_count' => $contact->messages_count,
            ]);
    }

    private function messagesPayload(?string $chatId)
    {
        return TelegramMessage::query()
            ->when($chatId, fn ($query) => $query->where('telegram_chat_id', $chatId))
            ->latest()
            ->limit(80)
            ->get()
            ->values()
            ->map(fn (TelegramMessage $message) => [
                'id' => $message->id,
                'direction' => $message->direction,
                'telegram_chat_id' => $message->telegram_chat_id,
                'telegram_user_id' => $message->telegram_user_id,
                'telegram_username' => $message->telegram_username,
                'message_type' => $message->message_type,
                'message_text' => $message->message_text,
                'callback_data' => $message->callback_data,
                'action_status' => $message->action_status,
                'payload_json' => $message->payload_json,
                'delivery_status' => $message->delivery_status,
                'sent_at' => optional($message->sent_at)->toIso8601String(),
                'received_at' => optional($message->received_at)->toIso8601String(),
                'created_at' => optional($message->created_at)->toIso8601String(),
                'related_child_id' => $message->related_child_id,
                'related_training_id' => $message->related_training_id,
            ]);
    }

    private function settingsPayload(): array
    {
        $settings = TelegramSetting::current();
        $tokenConfigured = filled($settings->bot_token) || filled(config('services.telegram.bot_token'));

        return [
            'bot_username' => $settings->bot_username ?: config('services.telegram.bot_username'),
            'bot_token_masked' => $settings->maskedToken(),
            'webhook_secret_masked' => $settings->maskedSecret(),
            'webhook_url' => $settings->webhook_url ?: config('services.telegram.webhook_url'),
            'default_chat_id' => $settings->default_chat_id,
            'enabled' => $settings->enabled,
            'has_bot_token' => $tokenConfigured,
            'webhook_secret_configured' => filled($settings->webhook_secret) || filled(config('services.telegram.webhook_secret')),
        ];
    }

    private function statsPayload(): array
    {
        $lastInbound = TelegramMessage::query()
            ->where('direction', TelegramMessage::DIRECTION_INBOUND)
            ->latest('received_at')
            ->first();

        return [
            'messages_today' => TelegramMessage::query()->whereDate('created_at', today())->count(),
            'last_inbound_text' => $lastInbound?->message_text,
            'last_inbound_at' => optional($lastInbound?->received_at)->toIso8601String(),
        ];
    }

    private function systemStatusPayload(): array
    {
        $lastInbound = TelegramMessage::query()
            ->where('direction', TelegramMessage::DIRECTION_INBOUND)
            ->latest('received_at')
            ->first();
        $lastOutbound = TelegramMessage::query()
            ->where('direction', TelegramMessage::DIRECTION_OUTBOUND)
            ->latest('sent_at')
            ->first();

        $settings = TelegramSetting::current();
        $info = null;
        try {
            $response = app(TelegramService::class)->getWebhookInfo();
            $info = $response?->json('result');
        } catch (\Throwable) {
            $info = null;
        }
        $configuredUrl = $settings->webhook_url ?: config('services.telegram.webhook_url');

        return [
            'webhook_url' => $configuredUrl,
            'webhook_registered' => filled($info['url'] ?? null) && ($info['url'] ?? null) === $configuredUrl,
            'pending_updates_count' => $info['pending_update_count'] ?? null,
            'last_webhook_error' => $info['last_error_message'] ?? null,
            'bot_reachable' => $info !== null || filled($settings->bot_token) || filled(config('services.telegram.bot_token')),
            'last_inbound_at' => optional($lastInbound?->received_at)->toIso8601String(),
            'last_outbound_at' => optional($lastOutbound?->sent_at)->toIso8601String(),
        ];
    }

    private function schedulerDiagnosticsPayload(): array
    {
        $lastReminder = TelegramReminderLog::query()->latest('sent_at')->latest('created_at')->first();
        $lastDinnerRun = SchedulerRun::query()->where('command', 'telegram:send-dinner-suggestions')->latest('ran_at')->first();
        $lastReminderRun = SchedulerRun::query()->where('command', 'telegram:send-due-reminders')->latest('ran_at')->first();
        $lastAnyRun = SchedulerRun::query()->latest('ran_at')->first();
        $settings = TelegramSetting::current();

        return [
            'app_url' => config('app.url'),
            'server_time' => now()->toIso8601String(),
            'app_timezone' => config('app.timezone'),
            'vietnam_time' => Carbon::now('Asia/Ho_Chi_Minh')->toIso8601String(),
            'bot_configured' => filled($settings->bot_token) || filled(config('services.telegram.bot_token')),
            'webhook_url' => $settings->webhook_url ?: config('services.telegram.webhook_url'),
            'scheduler_last_run_at' => optional($lastAnyRun?->ran_at)->toIso8601String(),
            'last_dinner_run' => $lastDinnerRun ? [
                'ran_at' => optional($lastDinnerRun->ran_at)->toIso8601String(),
                'status' => $lastDinnerRun->status,
                'summary' => $lastDinnerRun->output_summary,
                'error' => $lastDinnerRun->error_message,
            ] : null,
            'last_reminder_run' => $lastReminderRun ? [
                'ran_at' => optional($lastReminderRun->ran_at)->toIso8601String(),
                'status' => $lastReminderRun->status,
                'summary' => $lastReminderRun->output_summary,
                'error' => $lastReminderRun->error_message,
            ] : null,
            'last_reminder_sent_at' => optional($lastReminder?->sent_at)->toIso8601String(),
            'queue_connection' => config('queue.default'),
            'cron_hint' => '* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1',
            'recent_runs' => SchedulerRun::query()
                ->latest('ran_at')
                ->limit(8)
                ->get()
                ->map(fn (SchedulerRun $run) => [
                    'id' => $run->id,
                    'command' => $run->command,
                    'ran_at' => optional($run->ran_at)->toIso8601String(),
                    'status' => $run->status,
                    'summary' => $run->output_summary,
                    'error' => $run->error_message,
                ]),
        ];
    }

    private function reminderTestPayload(): array
    {
        return [
            'training_sessions' => TrainingSession::with('child:id,full_name,status')
                ->whereDate('session_date', today())
                ->whereNotNull('scheduled_time')
                ->latest('id')
                ->limit(10)
                ->get(['id', 'child_id', 'scheduled_time', 'status'])
                ->map(fn (TrainingSession $session) => [
                    'id' => $session->id,
                    'child_name' => $session->child?->full_name,
                    'scheduled_time' => $session->scheduled_time,
                    'status' => $session->status,
                ]),
            'meal_items' => MealPlanItem::query()
                ->where('day_of_week', today()->dayOfWeekIso)
                ->whereNotNull('scheduled_time')
                ->orderBy('scheduled_time')
                ->limit(10)
                ->get(['id', 'scheduled_time', 'title', 'meal_time']),
            'supplements' => SupplementSchedule::with('child:id,full_name,status')
                ->active()
                ->whereNotNull('scheduled_time')
                ->orderBy('scheduled_time')
                ->limit(10)
                ->get()
                ->map(fn (SupplementSchedule $schedule) => [
                    'id' => $schedule->id,
                    'child_name' => $schedule->child?->full_name,
                    'name' => $schedule->name,
                    'scheduled_time' => $schedule->scheduled_time,
                ]),
            'logs' => TelegramReminderLog::with('child:id,full_name')
                ->latest()
                ->limit(12)
                ->get()
                ->map(fn (TelegramReminderLog $log) => [
                    'id' => $log->id,
                    'child_name' => $log->child?->full_name,
                    'reminder_type' => $log->reminder_type,
                    'scheduled_for' => optional($log->scheduled_for)->toIso8601String(),
                    'reminder_due_at' => optional($log->reminder_due_at)->toIso8601String(),
                    'status' => $log->status,
                    'error_message' => $log->error_message,
                ]),
        ];
    }

    private function mealSuggestionTestPayload(): array
    {
        $children = Child::active()
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'status'])
            ->map(fn (Child $child) => [
                'id' => $child->id,
                'full_name' => $child->full_name,
                'status' => $child->status,
            ]);

        $previewChild = Child::active()->orderBy('full_name')->first();
        $service = app(TelegramMealSuggestionService::class);

        return [
            'children' => $children,
            'preview' => $service->previewPayload($previewChild),
            'logs' => TelegramMealSuggestionLog::with('child:id,full_name')
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn (TelegramMealSuggestionLog $log) => [
                    'id' => $log->id,
                    'child_name' => $log->child?->full_name,
                    'suggestion_date' => optional($log->suggestion_date)->toDateString(),
                    'sent_at' => optional($log->sent_at)->toIso8601String(),
                    'status' => $log->status,
                    'error_message' => $log->error_message,
                ]),
            'last_callback' => TelegramMessage::query()
                ->where('message_type', 'meal_suggestion_callback')
                ->latest()
                ->first()?->only(['message_text', 'callback_data', 'action_status']),
        ];
    }

    private function trainingTestPayload(): array
    {
        $children = Child::query()
            ->whereIn('status', [Child::STATUS_ACTIVE, Child::STATUS_PAUSED])
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'status'])
            ->map(fn (Child $child) => [
                'id' => $child->id,
                'full_name' => $child->full_name,
                'status' => $child->status,
            ]);

        $sessions = TrainingSession::query()
            ->with('child:id,full_name,status')
            ->whereDate('session_date', today())
            ->orderBy('scheduled_time')
            ->orderBy('id')
            ->get(['id', 'child_id', 'session_date', 'scheduled_time', 'status'])
            ->map(fn (TrainingSession $session) => [
                'id' => $session->id,
                'child_id' => $session->child_id,
                'child_name' => $session->child?->full_name,
                'status' => $session->status,
                'scheduled_time' => $session->scheduled_time,
            ]);

        $lastWebhook = TelegramMessage::query()
            ->whereIn('message_type', ['training_callback', 'callback', 'text'])
            ->latest()
            ->first();

        return [
            'children' => $children,
            'sessions' => $sessions,
            'last_webhook' => $lastWebhook ? [
                'id' => $lastWebhook->id,
                'message_text' => $lastWebhook->message_text,
                'callback_data' => $lastWebhook->callback_data,
                'action_status' => $lastWebhook->action_status,
                'created_at' => optional($lastWebhook->created_at)->toIso8601String(),
            ] : null,
        ];
    }
}
