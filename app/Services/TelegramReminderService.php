<?php

namespace App\Services;

use App\Models\Child;
use App\Models\MealLog;
use App\Models\MealPlanItem;
use App\Models\SupplementLog;
use App\Models\SupplementSchedule;
use App\Models\TelegramMessage;
use App\Models\TelegramReminderLog;
use App\Models\TelegramSetting;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use InvalidArgumentException;

class TelegramReminderService
{
    public function __construct(private readonly TelegramService $telegramService)
    {
    }

    public function collectDueReminders(): Collection
    {
        $logs = collect();

        TrainingSession::with(['child', 'items.exercise'])
            ->whereDate('session_date', today())
            ->whereNotNull('scheduled_time')
            ->whereHas('child', fn ($query) => $query->active())
            ->get()
            ->each(fn (TrainingSession $session) => $logs->push($this->createTrainingReminder($session)));

        SupplementSchedule::with('child')
            ->active()
            ->whereNotNull('scheduled_time')
            ->whereHas('child', fn ($query) => $query->active())
            ->get()
            ->each(fn (SupplementSchedule $schedule) => $logs->push($this->createSupplementReminder($schedule)));

        return $logs
            ->filter()
            ->where('status', TelegramReminderLog::STATUS_PENDING)
            ->filter(fn (TelegramReminderLog $log) => $log->reminder_due_at->lessThanOrEqualTo(now()))
            ->values();
    }

    public function sendDueReminders(): array
    {
        $pending = $this->collectDueReminders();
        $result = ['pending' => $pending->count(), 'sent' => 0, 'failed' => 0, 'skipped' => 0];

        foreach ($pending as $log) {
            $sent = $this->sendReminderLog($log);
            $result[$sent ? 'sent' : ($log->status === TelegramReminderLog::STATUS_SKIPPED ? 'skipped' : 'failed')]++;
        }

        return $result;
    }

    public function createTrainingReminder(TrainingSession $session): ?TelegramReminderLog
    {
        $session->loadMissing(['child', 'items.exercise']);
        $scheduledFor = $this->scheduledAt($session->session_date, $session->scheduled_time);

        return $this->createReminderLog(
            TelegramReminderLog::TYPE_TRAINING,
            $session->child,
            TrainingSession::class,
            $session->id,
            $scheduledFor
        );
    }

    public function createMealReminder(MealPlanItem|MealLog $meal): ?TelegramReminderLog
    {
        if ($meal instanceof MealLog) {
            $meal->loadMissing('child');
            $scheduledFor = $meal->scheduled_for;
            $child = $meal->child;
            $relatedId = $meal->id;
            $relatedType = MealLog::class;
        } else {
            $scheduledFor = $this->scheduledAt(today(), $meal->scheduled_time);
            $child = Child::active()->orderBy('id')->first();
            $relatedId = $meal->id;
            $relatedType = MealPlanItem::class;
        }

        return $this->createReminderLog(
            TelegramReminderLog::TYPE_MEAL,
            $child,
            $relatedType,
            $relatedId,
            $scheduledFor
        );
    }

    public function createSupplementReminder(SupplementSchedule $schedule): ?TelegramReminderLog
    {
        $schedule->loadMissing('child');

        return $this->createReminderLog(
            TelegramReminderLog::TYPE_SUPPLEMENT,
            $schedule->child,
            SupplementSchedule::class,
            $schedule->id,
            $this->scheduledAt(today(), $schedule->scheduled_time)
        );
    }

    public function sendReminderLog(TelegramReminderLog $log): bool
    {
        if ($log->status === TelegramReminderLog::STATUS_SENT) {
            return false;
        }

        if (blank($this->telegramService->botToken())) {
            return $this->markFailed($log, 'Chưa cấu hình Telegram Bot.');
        }

        $payload = $this->messagePayload($log);
        if (!$payload) {
            return $this->markFailed($log, 'Không tìm thấy dữ liệu nhắc lịch.');
        }

        $message = $this->telegramService->logOutboundMessage($log->telegram_chat_id, $payload['text'], [
            'message_type' => 'reminder_'.$log->reminder_type,
            'payload_json' => [
                'reply_markup' => $payload['reply_markup'],
                'reminder_log_id' => $log->id,
            ],
            'related_child_id' => $log->child_id,
            'related_training_id' => $log->reminder_type === TelegramReminderLog::TYPE_TRAINING ? $log->related_id : null,
        ]);

        $response = $this->telegramService->deliverLoggedMessage($message, $payload['reply_markup']);

        if ($response?->successful()) {
            $this->markSent($log);
            return true;
        }

        return $this->markFailed($log, 'Không gửi được tin nhắn Telegram.');
    }

    public function markSent(TelegramReminderLog $log): void
    {
        $log->update([
            'status' => TelegramReminderLog::STATUS_SENT,
            'sent_at' => now(),
            'error_message' => null,
        ]);
    }

    public function markFailed(TelegramReminderLog $log, string $message): bool
    {
        $log->update([
            'status' => TelegramReminderLog::STATUS_FAILED,
            'error_message' => $message,
        ]);

        return false;
    }

    public function handleSupplementCallback(array $callback): ?TelegramMessage
    {
        $data = (string) ($callback['data'] ?? '');
        if (!preg_match('/^supplement_schedule:(\d+):(taken|skipped)$/', $data, $matches)) {
            return null;
        }

        $schedule = SupplementSchedule::with('child')->find((int) $matches[1]);
        if (!$schedule) {
            return null;
        }

        $action = $matches[2];
        SupplementLog::updateOrCreate(
            [
                'supplement_schedule_id' => $schedule->id,
                'scheduled_for' => today()->toDateString(),
            ],
            [
                'child_id' => $schedule->child_id,
                'status' => $action === 'taken' ? 'taken' : 'skipped',
                'taken_at' => $action === 'taken' ? now() : null,
                'notes' => $action === 'taken' ? 'Ghi nhận từ Telegram.' : 'Bỏ qua từ Telegram.',
            ]
        );

        $message = $callback['message'] ?? [];
        $chat = $message['chat'] ?? [];
        $from = $callback['from'] ?? [];
        $chatId = (string) ($chat['id'] ?? $from['id'] ?? '');
        $text = $action === 'taken'
            ? "Đã bấm: Đã uống - {$schedule->name}"
            : "Đã bấm: Bỏ qua - {$schedule->name}";

        $inbound = TelegramMessage::create([
            'direction' => TelegramMessage::DIRECTION_INBOUND,
            'telegram_chat_id' => $chatId,
            'telegram_user_id' => isset($from['id']) ? (string) $from['id'] : null,
            'telegram_username' => $from['username'] ?? $chat['username'] ?? null,
            'message_type' => 'supplement_callback',
            'message_text' => $text,
            'callback_data' => $data,
            'action_status' => $action,
            'payload_json' => ['callback' => $callback],
            'delivery_status' => TelegramMessage::STATUS_RECEIVED,
            'received_at' => now(),
            'related_child_id' => $schedule->child_id,
        ]);

        if (filled($chatId)) {
            $this->telegramService->sendMessage($chatId, $action === 'taken'
                ? 'Đã ghi nhận bé đã uống/bổ sung.'
                : 'Đã ghi nhận bỏ qua lịch bổ sung hôm nay.'
            );
        }

        return $inbound;
    }

    public function supplementCallbackFeedbackText(string $action): string
    {
        return $action === 'taken' ? 'Đã ghi nhận: Đã uống' : 'Đã ghi nhận: Bỏ qua';
    }

    public function testReminder(string $type): TelegramReminderLog
    {
        return match ($type) {
            TelegramReminderLog::TYPE_TRAINING => $this->createTrainingReminder(
                TrainingSession::with(['child', 'items.exercise'])
                    ->whereNotNull('scheduled_time')
                    ->whereHas('child', fn ($query) => $query->active())
                    ->latest('id')
                    ->firstOrFail()
            ),
            TelegramReminderLog::TYPE_MEAL => throw new InvalidArgumentException('Lịch ăn dùng gợi ý bữa tối lúc 14:00, không dùng nhắc trước 30 phút.'),
            TelegramReminderLog::TYPE_SUPPLEMENT => $this->createSupplementReminder(
                SupplementSchedule::active()->whereNotNull('scheduled_time')->latest('id')->firstOrFail()
            ),
            default => throw new InvalidArgumentException('Loại nhắc lịch không hợp lệ.'),
        };
    }

    private function createReminderLog(string $type, ?Child $child, string $relatedType, int $relatedId, ?Carbon $scheduledFor): ?TelegramReminderLog
    {
        if (!$scheduledFor) {
            throw new InvalidArgumentException('Lịch này chưa có thời gian cụ thể.');
        }

        if (!$child?->isActive()) {
            return null;
        }

        $chatId = $this->resolveChatId($child);
        if (blank($chatId)) {
            return null;
        }

        return TelegramReminderLog::firstOrCreate(
            [
                'reminder_type' => $type,
                'related_id' => $relatedId,
                'reminder_due_at' => $scheduledFor->copy()->subMinutes(30),
                'telegram_chat_id' => (string) $chatId,
            ],
            [
                'child_id' => $child->id,
                'related_type' => $relatedType,
                'scheduled_for' => $scheduledFor,
                'status' => TelegramReminderLog::STATUS_PENDING,
            ]
        );
    }

    private function messagePayload(TelegramReminderLog $log): ?array
    {
        return match ($log->reminder_type) {
            TelegramReminderLog::TYPE_TRAINING => $this->trainingPayload($log),
            TelegramReminderLog::TYPE_MEAL => $this->mealPayload($log),
            TelegramReminderLog::TYPE_SUPPLEMENT => $this->supplementPayload($log),
            default => null,
        };
    }

    private function trainingPayload(TelegramReminderLog $log): ?array
    {
        $session = TrainingSession::with(['child', 'items.exercise'])->find($log->related_id);
        if (!$session) {
            return null;
        }

        $time = substr((string) $session->scheduled_time, 0, 5);
        $count = $session->items->count();
        $minutes = $session->total_minutes ?: $session->items->sum('duration_minutes');
        $text = "Nhắc lịch tập\n\nCòn 30 phút nữa đến lịch tập của bé {$session->child?->full_name}.\n\nGiờ tập: {$time}\nNội dung: {$count} bài tập\nThời lượng dự kiến: {$minutes} phút\n\nBấm bên dưới để mở checklist.";

        return [
            'text' => $text,
            'reply_markup' => [
                'inline_keyboard' => [
                    [['text' => 'Mở checklist', 'url' => URL::to('/today')]],
                ],
            ],
        ];
    }

    private function mealPayload(TelegramReminderLog $log): ?array
    {
        $item = MealPlanItem::with('template')->find($log->related_id);
        if (!$item) {
            return null;
        }

        $time = substr((string) $item->scheduled_time, 0, 5);
        $childName = $log->child?->full_name ?: 'bé';
        $text = "Gợi ý bữa tối\n\nĐây là gợi ý chuẩn bị bữa tối cho bé {$childName}.\n\nGiờ ăn dự kiến: {$time}\nBữa: {$item->title}\nMục tiêu: hỗ trợ tiêu hóa và thói quen ăn uống";

        return [
            'text' => $text,
            'reply_markup' => [
                'inline_keyboard' => [
                    [['text' => 'Xem lịch ăn', 'url' => URL::to('/meal-plans')]],
                ],
            ],
        ];
    }

    private function supplementPayload(TelegramReminderLog $log): ?array
    {
        $schedule = SupplementSchedule::with('child')->find($log->related_id);
        if (!$schedule) {
            return null;
        }

        $time = substr((string) $schedule->scheduled_time, 0, 5);
        $note = $schedule->dosage_note ?: 'Không có ghi chú liều dùng.';
        $text = "Nhắc lịch bổ sung\n\nCòn 30 phút nữa đến lịch của bé {$schedule->child?->full_name}.\n\nThời gian: {$time}\nTên: {$schedule->name}\nGhi chú: {$note}\n\nLưu ý: liều dùng theo hướng dẫn của bác sĩ hoặc nhãn sản phẩm.";

        return [
            'text' => $text,
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => 'Đã uống', 'callback_data' => "supplement_schedule:{$schedule->id}:taken"],
                        ['text' => 'Bỏ qua', 'callback_data' => "supplement_schedule:{$schedule->id}:skipped"],
                    ],
                ],
            ],
        ];
    }

    private function scheduledAt(mixed $date, ?string $time): ?Carbon
    {
        if (blank($time)) {
            return null;
        }

        return Carbon::parse(Carbon::parse($date)->toDateString().' '.substr((string) $time, 0, 5));
    }

    private function resolveChatId(?Child $child = null): ?string
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
