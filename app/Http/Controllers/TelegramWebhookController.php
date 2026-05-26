<?php

namespace App\Http\Controllers;

use App\Jobs\SendTelegramMessageJob;
use App\Services\TelegramCommandService;
use App\Services\TelegramMealSuggestionService;
use App\Services\TelegramReminderService;
use App\Services\TelegramService;
use App\Services\TelegramTrainingNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        TelegramService $telegramService,
        TelegramCommandService $telegramCommandService,
        TelegramTrainingNotificationService $trainingNotificationService,
        TelegramReminderService $reminderService,
        TelegramMealSuggestionService $mealSuggestionService
    ): JsonResponse {
        Log::info('Telegram webhook received', [
            'has_message' => $request->has('message'),
            'has_callback_query' => $request->has('callback_query'),
            'callback_data' => $request->input('callback_query.data'),
        ]);

        if (!$this->hasValidSecret($request, $telegramService)) {
            Log::info('Telegram webhook rejected by secret');

            return response()->json(['message' => 'Không được phép truy cập.'], 403);
        }

        $telegramService->processWebhook($request->all());

        if ($request->has('message')) {
            $this->handleMessage($request, $telegramService, $telegramCommandService);
        }

        if ($request->has('callback_query')) {
            $this->handleCallback($request, $telegramService, $trainingNotificationService, $reminderService, $mealSuggestionService);
        }

        return response()->json(['ok' => true]);
    }

    protected function hasValidSecret(Request $request, TelegramService $telegramService): bool
    {
        $secret = $telegramService->webhookSecret();

        if (blank($secret)) {
            return app()->environment(['local', 'testing']);
        }

        return hash_equals((string) $secret, (string) (
            $request->header('X-Telegram-Bot-Api-Secret-Token')
            ?: $request->query('secret')
        ));
    }

    protected function handleMessage(
        Request $request,
        TelegramService $telegramService,
        TelegramCommandService $telegramCommandService
    ): void {
        $chatId = $request->input('message.chat.id');
        $text = trim((string) $request->input('message.text', ''));

        if (blank($chatId)) {
            return;
        }

        if (Str::startsWith($text, '/start parent_')) {
            $token = Str::after($text, '/start parent_');
            $user = $telegramService->linkParent($token, $chatId);

            if ($user) {
                SendTelegramMessageJob::dispatch(
                    (string) $chatId,
                    'Đã kết nối Telegram. Bạn sẽ nhận nhắc lịch hằng ngày.',
                    $telegramService->openChecklistKeyboard()
                );
            }

            return;
        }

        if ($telegramCommandService->handleMessage($request->input('message', []))) {
            return;
        }

        if (preg_match('/^\/(done|refuse)\s+(\d+)$/', $text, $matches)) {
            $action = $matches[1] === 'done' ? 'checklist_done' : 'checklist_refuse';
            $itemId = $matches[2];
            $this->processChecklistUpdate($chatId, "{$action}:{$itemId}", $telegramService);
        }
    }

    protected function handleCallback(
        Request $request,
        TelegramService $telegramService,
        TelegramTrainingNotificationService $trainingNotificationService,
        TelegramReminderService $reminderService,
        TelegramMealSuggestionService $mealSuggestionService
    ): void {
        $callbackQueryId = $request->input('callback_query.id');
        $chatId = $request->input('callback_query.message.chat.id');
        $data = (string) $request->input('callback_query.data', '');

        if (blank($chatId) || blank($data)) {
            return;
        }

        if (Str::startsWith($data, 'training_session:') || Str::startsWith($data, 'training_item:')) {
            $parts = explode(':', $data);
            $action = $parts[2] ?? '';
            Log::info('Telegram training callback dispatch', [
                'callback_query_id' => $callbackQueryId,
                'callback_data' => $data,
                'session_id' => $parts[1] ?? null,
                'action' => $action,
            ]);

            if ($callbackQueryId) {
                $telegramService->answerCallbackQuery(
                    $callbackQueryId,
                    $trainingNotificationService->callbackFeedbackText($action)
                );
            }

            $trainingNotificationService->processCallback($request->input('callback_query', []));

            return;
        }

        if (Str::startsWith($data, 'supplement_schedule:')) {
            $parts = explode(':', $data);
            $action = $parts[2] ?? '';

            if ($callbackQueryId) {
                $telegramService->answerCallbackQuery(
                    $callbackQueryId,
                    $reminderService->supplementCallbackFeedbackText($action)
                );
            }

            $reminderService->handleSupplementCallback($request->input('callback_query', []));

            return;
        }

        if (Str::startsWith($data, 'meal_suggestion:')) {
            $parts = explode(':', $data);
            $action = $parts[3] ?? '';

            if ($callbackQueryId) {
                $telegramService->answerCallbackQuery(
                    $callbackQueryId,
                    $mealSuggestionService->callbackFeedbackText($action)
                );
            }

            $mealSuggestionService->handleCallback($request->input('callback_query', []));

            return;
        }

        if (Str::startsWith($data, 'checklist_note:') || Str::startsWith($data, 'note:')) {
            $telegramService->answerCallbackQuery($callbackQueryId);
            SendTelegramMessageJob::dispatch(
                (string) $chatId,
                'Vui lòng mở checklist để ghi chú nhanh cho bài tập.',
                $telegramService->openChecklistKeyboard()
            );

            return;
        }

        $this->processChecklistUpdate($chatId, $data, $telegramService, $callbackQueryId);
    }

    protected function processChecklistUpdate(string|int $chatId, string $data, TelegramService $telegramService, ?string $callbackQueryId = null): void
    {
        $item = $telegramService->updateChecklistFromCallback($data);

        if ($callbackQueryId) {
            $telegramService->answerCallbackQuery($callbackQueryId);
        }

        if ($item) {
            $exerciseName = $item->trainingSessionItem?->exercise?->title ?: 'bài tập';
            $statusText = $item->status === 'completed' ? '✅ Đã tập xong' : '❌ Bé từ chối';
            $message = "{$statusText}: {$exerciseName}";

            SendTelegramMessageJob::dispatch((string) $chatId, $message, $telegramService->openChecklistKeyboard());
        }
    }
}
