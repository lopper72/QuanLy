<?php

namespace App\Services;

use App\Models\Child;
use App\Models\TelegramMessage;
use App\Models\TelegramSetting;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class TelegramTrainingNotificationService
{
    public const ACTION_COMPLETED = 'completed';
    public const ACTION_NOT_COMPLETED = 'not_completed';
    public const ACTION_SKIPPED = 'skipped';
    public const ACTION_NEED_HELP = 'need_help';

    public function __construct(private readonly TelegramService $telegramService)
    {
    }

    public function sendTodayTraining(Child $child): TelegramMessage
    {
        $chatId = $this->resolveChatId();
        if (blank($chatId)) {
            throw new InvalidArgumentException('Chưa có mã hội thoại Telegram cho phụ huynh.');
        }

        return $this->sendTodayTrainingToChat($child, $chatId);
    }

    public function sendTodayTrainingToChat(Child $child, string|int $chatId): TelegramMessage
    {
        $child->loadMissing(['trainingSessions.items.exercise']);

        if ($child->isVoided()) {
            throw new InvalidArgumentException('Trẻ đã ngừng can thiệp nên không thể nhận lịch tập mới.');
        }

        if (!$child->isActive()) {
            throw new InvalidArgumentException('Trẻ đang tạm nghỉ. Vui lòng xác nhận trước khi gửi lịch tập.');
        }

        if (blank($this->telegramService->botToken())) {
            throw new InvalidArgumentException('Chưa cấu hình bot Telegram.');
        }

        if (blank($chatId)) {
            throw new InvalidArgumentException('Chưa có mã hội thoại Telegram cho phụ huynh.');
        }

        $sessions = TrainingSession::query()
            ->with(['child', 'items.exercise'])
            ->where('child_id', $child->id)
            ->whereDate('session_date', today())
            ->orderBy('scheduled_time')
            ->orderBy('id')
            ->get();

        if ($sessions->isEmpty()) {
            throw new InvalidArgumentException('Chưa có buổi tập hôm nay cho trẻ này.');
        }

        $primarySession = $sessions->first();
        $messageText = $this->buildTodayTrainingMessage($child, $sessions);
        $keyboard = $this->trainingKeyboard($primarySession);

        $message = $this->telegramService->logOutboundMessage((string) $chatId, $messageText, [
            'message_type' => 'training_schedule',
            'payload_json' => [
                'reply_markup' => $keyboard,
                'session_ids' => $sessions->pluck('id')->all(),
            ],
            'related_child_id' => $child->id,
            'related_training_id' => $primarySession->id,
        ]);

        $this->telegramService->deliverLoggedMessage($message, $keyboard);

        return $message->refresh();
    }

    public function processCallback(array $callback): ?TelegramMessage
    {
        $data = (string) ($callback['data'] ?? '');

        if (!preg_match('/^training_(session|item):(\d+):(completed|not_completed|skipped|need_help)$/', $data, $matches)) {
            Log::info('Telegram training callback ignored', [
                'callback_data' => $data,
            ]);

            return null;
        }

        $targetType = $matches[1];
        $targetId = (int) $matches[2];
        $action = $matches[3];
        $item = null;
        $session = null;

        if ($targetType === 'item') {
            $item = \App\Models\TrainingSessionItem::with(['trainingSession.child', 'exercise'])->find($targetId);
            $session = $item?->trainingSession;
        } else {
            $session = TrainingSession::with(['child', 'items.exercise'])->find($targetId);
        }

        if (!$session) {
            Log::info('Telegram training callback session not found', [
                'callback_data' => $data,
                'target_type' => $targetType,
                'target_id' => $targetId,
            ]);

            return null;
        }

        $message = $callback['message'] ?? [];
        $chat = $message['chat'] ?? [];
        $from = $callback['from'] ?? [];
        $chatId = (string) ($chat['id'] ?? $from['id'] ?? '');
        $receivedAt = isset($message['date'])
            ? Carbon::createFromTimestamp((int) $message['date'])
            : now();

        $beforeStatus = $session->status;
        Log::info('Telegram training callback received', [
            'callback_data' => $data,
            'session_id' => $session->id,
            'item_id' => $item?->id,
            'action' => $action,
            'status_before' => $beforeStatus,
        ]);

        $afterStatus = $item
            ? $this->applyActionToItem($item, $action)
            : $this->applyActionToSession($session, $action);
        $label = $this->actionLabel($action);
        $targetLabel = $item?->exercise?->title ?: 'buổi tập';

        $inbound = TelegramMessage::create([
            'direction' => TelegramMessage::DIRECTION_INBOUND,
            'telegram_chat_id' => $chatId,
            'telegram_user_id' => isset($from['id']) ? (string) $from['id'] : null,
            'telegram_username' => $from['username'] ?? $chat['username'] ?? null,
            'message_type' => 'training_callback',
            'message_text' => "Đã bấm: {$label} - {$targetLabel}",
            'callback_data' => $data,
            'action_status' => $action,
            'payload_json' => [
                'callback' => $callback,
                'status_before' => $beforeStatus,
                'status_after' => $afterStatus,
                'target_type' => $targetType,
                'target_id' => $targetId,
            ],
            'delivery_status' => TelegramMessage::STATUS_RECEIVED,
            'received_at' => $receivedAt,
            'related_child_id' => $session->child_id,
            'related_training_id' => $session->id,
        ]);

        if (filled($chatId)) {
            $this->telegramService->syncTelegramContact($chat, $from, $receivedAt);
            $this->telegramService->sendMessage((string) $chatId, $this->confirmationMessage($action));
        }

        Log::info('Telegram training callback processed', [
            'callback_data' => $data,
            'session_id' => $session->id,
            'item_id' => $item?->id,
            'action' => $action,
            'status_before' => $beforeStatus,
            'status_after' => $afterStatus,
            'telegram_message_id' => $inbound->id,
        ]);

        return $inbound;
    }

    public function simulateCallback(TrainingSession $session, string $action): TelegramMessage
    {
        $chatId = $this->resolveChatId() ?: 'test-chat';

        return $this->processCallback([
            'id' => 'simulate-'.now()->timestamp,
            'data' => "training_session:{$session->id}:{$action}",
            'from' => [
                'id' => 'simulate-user',
                'username' => 'phu_huynh',
                'first_name' => 'Phụ huynh',
            ],
            'message' => [
                'date' => now()->timestamp,
                'chat' => [
                    'id' => $chatId,
                    'first_name' => 'Phụ huynh',
                ],
            ],
        ]);
    }

    public function buildTodayTrainingMessage(Child $child, $sessions): string
    {
        $lines = [
            "📅 Lịch tập hôm nay của bé {$child->full_name}",
            '',
            'Ngày: '.today()->format('d/m/Y'),
            '',
        ];

        $index = 1;
        foreach ($sessions as $session) {
            if ($session->scheduled_time) {
                $lines[] = 'Giờ tập: '.substr((string) $session->scheduled_time, 0, 5);
            }

            foreach ($session->items as $item) {
                $exerciseTitle = $item->exercise?->title ?: 'Bài tập chưa xác định';
                $minutes = $item->duration_minutes ?: $item->exercise?->estimated_minutes ?: 0;
                $lines[] = "{$index}. {$exerciseTitle} - {$minutes} phút";
                $index++;
            }
        }

        if ($index === 1) {
            $lines[] = 'Chưa có bài tập chi tiết.';
        }

        $lines[] = '';
        $lines[] = 'Phụ huynh vui lòng xác nhận sau khi hoàn thành.';

        return implode("\n", $lines);
    }

    public function trainingKeyboard(TrainingSession $session): array
    {
        return [
            'inline_keyboard' => $this->trainingItemKeyboardRows($session),
        ];
    }

    protected function trainingItemKeyboardRows(TrainingSession $session): array
    {
        $session->loadMissing('items.exercise');
        $rows = [];

        foreach ($session->items->sortBy('sort_order')->values() as $index => $item) {
            $number = $index + 1;
            $rows[] = [
                ['text' => "{$number} ✅ Hoàn thành", 'callback_data' => "training_item:{$item->id}:completed"],
                ['text' => "{$number} ⏳ Chưa hoàn thành", 'callback_data' => "training_item:{$item->id}:not_completed"],
            ];
            $rows[] = [
                ['text' => "{$number} ⏭ Bỏ qua", 'callback_data' => "training_item:{$item->id}:skipped"],
                ['text' => "{$number} 💬 Hỗ trợ", 'callback_data' => "training_item:{$item->id}:need_help"],
            ];
        }

        if ($rows === []) {
            $rows[] = [
                ['text' => '✅ Đã hoàn thành', 'callback_data' => "training_session:{$session->id}:completed"],
                ['text' => '💬 Cần hỗ trợ', 'callback_data' => "training_session:{$session->id}:need_help"],
            ];
        }

        return $rows;
    }

    public function actionLabel(string $action): string
    {
        return match ($action) {
            self::ACTION_COMPLETED => 'Hoàn thành',
            self::ACTION_NOT_COMPLETED => 'Chưa hoàn thành',
            self::ACTION_SKIPPED => 'Bỏ qua',
            self::ACTION_NEED_HELP => 'Cần hỗ trợ',
            default => 'Không xác định',
        };
    }

    public function callbackFeedbackText(string $action): string
    {
        return match ($action) {
            self::ACTION_COMPLETED => 'Đã ghi nhận: Hoàn thành',
            self::ACTION_NOT_COMPLETED => 'Đã ghi nhận: Chưa hoàn thành',
            self::ACTION_SKIPPED => 'Đã ghi nhận: Bỏ qua',
            self::ACTION_NEED_HELP => 'Đã ghi nhận: Cần hỗ trợ',
            default => 'Đã ghi nhận phản hồi.',
        };
    }

    public function confirmationMessage(string $action): string
    {
        return match ($action) {
            self::ACTION_COMPLETED => '✅ Đã ghi nhận hoàn thành buổi tập.',
            self::ACTION_NOT_COMPLETED => '⏳ Đã ghi nhận buổi tập chưa hoàn thành.',
            self::ACTION_SKIPPED => '⏭ Đã ghi nhận bỏ qua buổi tập hôm nay.',
            self::ACTION_NEED_HELP => '💬 Đã ghi nhận cần hỗ trợ. Người phụ trách sẽ xem lại.',
            default => 'Đã ghi nhận phản hồi.',
        };
    }

    protected function applyActionToSession(TrainingSession $session, string $action): string
    {
        return DB::transaction(function () use ($session, $action) {
            $status = match ($action) {
                self::ACTION_COMPLETED => 'completed',
                self::ACTION_NOT_COMPLETED => 'in_progress',
                self::ACTION_SKIPPED => 'skipped',
                self::ACTION_NEED_HELP => 'need_help',
            };

            $session->update(['status' => $status]);

            if ($action === self::ACTION_COMPLETED) {
                $session->items()->update(['completion_status' => 'completed']);
            }

            if ($action === self::ACTION_SKIPPED) {
                $session->items()->update(['completion_status' => 'skipped']);
            }

            if ($action === self::ACTION_NOT_COMPLETED) {
                $session->items()->whereIn('completion_status', ['pending', 'not_started', 'planned', 'in_progress'])->update([
                    'completion_status' => 'missed',
                ]);
            }

            return $status;
        });
    }

    protected function applyActionToItem(\App\Models\TrainingSessionItem $item, string $action): string
    {
        return DB::transaction(function () use ($item, $action) {
            $itemStatus = match ($action) {
                self::ACTION_COMPLETED => 'completed',
                self::ACTION_NOT_COMPLETED => 'partially_completed',
                self::ACTION_SKIPPED => 'skipped',
                self::ACTION_NEED_HELP => 'partially_completed',
            };

            $payload = ['completion_status' => $itemStatus];
            if ($action === self::ACTION_NEED_HELP) {
                $payload['therapist_note'] = trim(($item->therapist_note ? $item->therapist_note.PHP_EOL : '').'Phụ huynh cần hỗ trợ qua Telegram.');
            }

            $item->update($payload);

            $session = $item->trainingSession()->with('items')->first();
            if (!$session) {
                return 'planned';
            }

            if ($action === self::ACTION_NEED_HELP) {
                $session->update(['status' => 'need_help']);

                return 'need_help';
            }

            $items = $session->items;
            $allCompleted = $items->isNotEmpty() && $items->every(fn ($sessionItem) => $sessionItem->completion_status === 'completed');
            $allSkipped = $items->isNotEmpty() && $items->every(fn ($sessionItem) => $sessionItem->completion_status === 'skipped');
            $allProcessed = $items->isNotEmpty() && $items->every(fn ($sessionItem) => in_array($sessionItem->completion_status, ['completed', 'skipped'], true));

            $status = match (true) {
                $allCompleted => 'completed',
                $allSkipped => 'skipped',
                $allProcessed => 'completed',
                default => 'in_progress',
            };

            $session->update(['status' => $status]);

            return $status;
        });
    }

    protected function resolveChatId(): ?string
    {
        $settings = TelegramSetting::current();
        if (filled($settings->default_chat_id)) {
            return $settings->default_chat_id;
        }

        return User::query()
            ->whereNotNull('telegram_chat_id')
            ->where('telegram_notifications_enabled', true)
            ->value('telegram_chat_id');
    }
}
