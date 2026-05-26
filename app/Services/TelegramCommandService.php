<?php

namespace App\Services;

use App\Models\Child;
use App\Models\MealLog;
use App\Models\MealPlanItem;
use App\Models\SupplementLog;
use App\Models\SupplementSchedule;
use App\Models\TelegramMessage;
use App\Models\TrainingSession;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TelegramCommandService
{
    public function __construct(
        private readonly TelegramService $telegramService,
        private readonly TelegramTrainingNotificationService $trainingNotificationService,
        private readonly TelegramMealSuggestionService $mealSuggestionService
    ) {
    }

    public function handleMessage(array $message): bool
    {
        $chatId = (string) data_get($message, 'chat.id', '');
        $text = trim((string) data_get($message, 'text', ''));

        if (blank($chatId) || !Str::startsWith($text, '/')) {
            return false;
        }

        [$command, $argument] = $this->parseCommand($text);

        return match ($command) {
            'help', 'lenh' => $this->sendHelp($chatId),
            'today', 'tap' => $this->sendTodayTraining($chatId),
            'full' => $this->sendFullSchedule($chatId),
            'thuoc' => $this->sendSupplements($chatId),
            'an' => $this->sendMeals($chatId),
            'doimon' => $this->sendAlternativeDinner($chatId),
            'tiendo' => $this->sendProgress($chatId),
            'id' => $this->sendChatId($chatId),
            'hotro' => $this->sendSupportRequest($chatId, $argument),
            default => false,
        };
    }

    public function commands(): array
    {
        return [
            ['command' => 'help', 'description' => 'Xem danh sách lệnh'],
            ['command' => 'today', 'description' => 'Xem lịch tập hôm nay'],
            ['command' => 'tap', 'description' => 'Xem lịch tập hôm nay'],
            ['command' => 'full', 'description' => 'Xem toàn bộ lịch hôm nay'],
            ['command' => 'thuoc', 'description' => 'Xem lịch thuốc và bổ sung'],
            ['command' => 'an', 'description' => 'Xem lịch ăn uống hôm nay'],
            ['command' => 'doimon', 'description' => 'Gợi ý món tối thay thế'],
            ['command' => 'tiendo', 'description' => 'Xem tiến độ hôm nay'],
            ['command' => 'id', 'description' => 'Xem mã hội thoại Telegram'],
            ['command' => 'hotro', 'description' => 'Báo cần hỗ trợ'],
        ];
    }

    private function parseCommand(string $text): array
    {
        $parts = preg_split('/\s+/', $text, 2);
        $rawCommand = ltrim((string) ($parts[0] ?? ''), '/');
        $command = Str::of($rawCommand)->before('@')->lower()->toString();

        return [$command, trim((string) ($parts[1] ?? ''))];
    }

    private function sendHelp(string $chatId): bool
    {
        $lines = [
            'Các lệnh Telegram đang hỗ trợ:',
            '',
            '/full - Xem toàn bộ lịch hôm nay',
            '/today hoặc /tap - Xem lịch tập hôm nay',
            '/thuoc - Xem lịch thuốc và bổ sung',
            '/an - Xem lịch ăn uống hôm nay',
            '/doimon - Gợi ý món tối thay thế',
            '/tiendo - Xem tiến độ hôm nay',
            '/id - Xem mã hội thoại Telegram',
            '/hotro - Báo cần hỗ trợ',
        ];

        $this->telegramService->sendMessage($chatId, implode("\n", $lines));

        return true;
    }

    private function sendTodayTraining(string $chatId): bool
    {
        $children = $this->activeChildren()
            ->filter(fn (Child $child) => $child->trainingSessions->isNotEmpty())
            ->values();

        if ($children->isEmpty()) {
            $this->telegramService->sendMessage($chatId, 'Hôm nay chưa có lịch tập nào.');

            return true;
        }

        foreach ($children as $child) {
            try {
                $this->trainingNotificationService->sendTodayTrainingToChat($child, $chatId);
            } catch (\InvalidArgumentException $exception) {
                $this->telegramService->sendMessage($chatId, $exception->getMessage());
            }
        }

        return true;
    }

    private function sendFullSchedule(string $chatId): bool
    {
        $children = $this->activeChildren();

        if ($children->isEmpty()) {
            $this->telegramService->sendMessage($chatId, 'Hôm nay chưa có lịch nào cho bé.');

            return true;
        }

        foreach ($children as $child) {
            $text = $this->buildFullScheduleMessage($child);
            $this->telegramService->sendMessage($chatId, $text);
        }

        return true;
    }

    private function sendSupplements(string $chatId): bool
    {
        $children = $this->activeChildren();

        if ($children->isEmpty()) {
            $this->telegramService->sendMessage($chatId, 'Chưa có trẻ đang can thiệp để xem lịch bổ sung.');

            return true;
        }

        foreach ($children as $child) {
            $this->telegramService->sendMessage(
                $chatId,
                $this->buildSupplementMessage($child),
                $this->supplementKeyboard($child)
            );
        }

        return true;
    }

    private function sendMeals(string $chatId): bool
    {
        $children = $this->mealSuggestionService->activeChildrenForChat($chatId);

        if ($children->isEmpty()) {
            $this->telegramService->sendMessage($chatId, 'Chưa liên kết Telegram với hồ sơ trẻ. Vui lòng liên kết trước khi sử dụng lệnh này.');

            return true;
        }

        foreach ($children as $child) {
            $this->mealSuggestionService->sendTodayMealScheduleForChat($chatId, $child, today());
        }

        return true;
    }

    private function sendAlternativeDinner(string $chatId): bool
    {
        $children = $this->mealSuggestionService->activeChildrenForChat($chatId);

        if ($children->isEmpty()) {
            $this->telegramService->sendMessage($chatId, 'Chưa liên kết Telegram với hồ sơ trẻ. Vui lòng liên kết trước khi sử dụng lệnh này.');

            return true;
        }

        foreach ($children as $child) {
            $this->mealSuggestionService->sendAlternativeDinnerForChat($chatId, $child, today());
        }

        return true;
    }

    private function sendProgress(string $chatId): bool
    {
        $children = $this->activeChildren();

        if ($children->isEmpty()) {
            $this->telegramService->sendMessage($chatId, 'Chưa có trẻ đang can thiệp để xem tiến độ.');

            return true;
        }

        foreach ($children as $child) {
            $this->telegramService->sendMessage($chatId, $this->buildProgressMessage($child));
        }

        return true;
    }

    private function sendChatId(string $chatId): bool
    {
        $this->telegramService->sendMessage($chatId, "Mã hội thoại Telegram của bạn là: {$chatId}");

        return true;
    }

    private function sendSupportRequest(string $chatId, string $argument): bool
    {
        TelegramMessage::create([
            'direction' => TelegramMessage::DIRECTION_INBOUND,
            'telegram_chat_id' => $chatId,
            'message_type' => 'support_request',
            'message_text' => trim($argument) !== '' ? $argument : 'Phụ huynh cần hỗ trợ.',
            'payload_json' => ['source' => 'telegram_command', 'command' => '/hotro'],
            'delivery_status' => TelegramMessage::STATUS_RECEIVED,
            'received_at' => now(),
        ]);

        $this->telegramService->sendMessage(
            $chatId,
            'Đã ghi nhận yêu cầu hỗ trợ. Người phụ trách sẽ xem lại và phản hồi khi có thể.'
        );

        return true;
    }

    private function activeChildren(): Collection
    {
        return Child::active()
            ->with([
                'trainingSessions' => fn ($query) => $query
                    ->with('items.exercise')
                    ->whereDate('session_date', today())
                    ->orderBy('scheduled_time')
                    ->orderBy('id'),
                'supplementSchedules' => fn ($query) => $query
                    ->active()
                    ->with(['logs' => fn ($logQuery) => $logQuery->whereDate('scheduled_for', today())])
                    ->where(fn ($dateQuery) => $dateQuery
                        ->whereNull('start_date')
                        ->orWhereDate('start_date', '<=', today()))
                    ->where(fn ($dateQuery) => $dateQuery
                        ->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', today()))
                    ->orderBy('scheduled_time')
                    ->orderBy('name'),
                'mealLogs' => fn ($query) => $query
                    ->with('item.template')
                    ->whereDate('meal_date', today())
                    ->orderBy('scheduled_for')
                    ->orderBy('id'),
            ])
            ->orderBy('full_name')
            ->get();
    }

    private function buildFullScheduleMessage(Child $child): string
    {
        $lines = [
            "📌 Lịch hôm nay của bé {$child->full_name}",
            '',
            '🧩 Lịch tập',
            ...$this->trainingLines($child),
            '',
            '🍽 Lịch ăn',
            ...$this->mealLines($child),
            '',
            '💊 Lịch bổ sung',
            ...$this->supplementLines($child),
            '',
            'Gõ /tiendo để xem bé đã hoàn thành những gì hôm nay.',
        ];

        return implode("\n", $lines);
    }

    private function buildSupplementMessage(Child $child): string
    {
        return implode("\n", [
            "💊 Lịch thuốc/bổ sung hôm nay của bé {$child->full_name}",
            '',
            ...$this->supplementLines($child, true),
            '',
            'Thông tin này chỉ dùng để nhắc lịch. Liều dùng cần theo hướng dẫn của bác sĩ hoặc nhãn sản phẩm.',
        ]);
    }

    private function buildMealMessage(Child $child): string
    {
        return implode("\n", [
            "🍽 Lịch ăn uống hôm nay của bé {$child->full_name}",
            '',
            ...$this->mealLines($child, true),
            '',
            'Nhắc nhẹ: cho bé uống nước chia nhỏ trong ngày và duy trì thói quen đi vệ sinh sau bữa ăn nếu phù hợp.',
        ]);
    }

    private function buildProgressMessage(Child $child): string
    {
        $trainingItems = $child->trainingSessions->flatMap->items;
        $trainingCompleted = $trainingItems->where('completion_status', 'completed')->count();
        $trainingTotal = $trainingItems->count();

        $supplementLogs = $child->supplementSchedules->map(fn (SupplementSchedule $schedule) => $schedule->logs->first())->filter();
        $supplementTaken = $supplementLogs->where('status', 'taken')->count();
        $supplementSkipped = $supplementLogs->where('status', 'skipped')->count();
        $supplementTotal = $child->supplementSchedules->count();

        $mealLogs = $child->mealLogs;
        $mealsDone = $mealLogs->where('status', 'done')->count();
        $mealsSkipped = $mealLogs->where('status', 'skipped')->count();
        $mealTotal = max($mealLogs->count(), $this->todayMealItems()->count());

        return implode("\n", [
            "📊 Tiến độ hôm nay của bé {$child->full_name}",
            '',
            "🧩 Bài tập: {$trainingCompleted}/{$trainingTotal} đã hoàn thành",
            "💊 Bổ sung: {$supplementTaken}/{$supplementTotal} đã uống, {$supplementSkipped} bỏ qua",
            "🍽 Bữa ăn: {$mealsDone}/{$mealTotal} đã ghi nhận, {$mealsSkipped} bỏ qua",
        ]);
    }

    private function trainingLines(Child $child): array
    {
        if ($child->trainingSessions->isEmpty()) {
            return ['Chưa có lịch tập hôm nay.'];
        }

        return $child->trainingSessions
            ->map(function (TrainingSession $session) {
                $time = $this->displayTime($session->scheduled_time);
                $titles = $session->items
                    ->map(fn ($item) => $item->exercise?->title ?: 'Bài tập chưa xác định')
                    ->filter()
                    ->implode(', ');

                return "{$time} - ".($titles ?: 'Chưa có bài tập chi tiết');
            })
            ->all();
    }

    private function supplementLines(Child $child, bool $withStatus = false): array
    {
        if ($child->supplementSchedules->isEmpty()) {
            return ['Chưa có lịch thuốc/bổ sung hôm nay.'];
        }

        return $child->supplementSchedules
            ->map(function (SupplementSchedule $schedule) use ($withStatus) {
                $status = $withStatus ? ' - '.$this->supplementStatusLabel($schedule->logs->first()?->status) : '';
                $note = $schedule->dosage_note ? " ({$schedule->dosage_note})" : '';

                return "{$this->displaySupplementTime($schedule)} - {$schedule->name}{$note}{$status}";
            })
            ->all();
    }

    private function mealLines(Child $child, bool $withDetails = false): array
    {
        $logs = $child->mealLogs;

        if ($logs->isNotEmpty()) {
            return $logs
                ->map(function (MealLog $log) use ($withDetails) {
                    $item = $log->item;
                    $time = $log->scheduled_for
                        ? $log->scheduled_for->format('H:i')
                        : $this->displayTime($item?->scheduled_time);
                    $foods = collect($item?->foods_json ?? [])->implode(', ');
                    $status = $withDetails ? ' - '.$this->mealStatusLabel($log->status) : '';

                    return "{$time} - ".($item?->title ?: 'Bữa ăn đã ghi nhận').($foods ? ": {$foods}" : '').$status;
                })
                ->all();
        }

        $items = $this->todayMealItems();
        if ($items->isEmpty()) {
            return ['Chưa có lịch ăn hôm nay.'];
        }

        return $items
            ->map(function (MealPlanItem $item) use ($withDetails) {
                $foods = collect($item->foods_json ?? [])->implode(', ');
                $detail = $withDetails && $item->constipation_support_note
                    ? " - {$item->constipation_support_note}"
                    : '';

                return "{$this->displayTime($item->scheduled_time)} - {$item->title}".($foods ? ": {$foods}" : '').$detail;
            })
            ->all();
    }

    private function todayMealItems(): Collection
    {
        return MealPlanItem::with('template')
            ->where('day_of_week', today()->dayOfWeekIso)
            ->whereHas('template', fn ($query) => $query->active())
            ->orderBy('scheduled_time')
            ->orderBy('meal_time')
            ->get();
    }

    private function supplementKeyboard(Child $child): array
    {
        $rows = $child->supplementSchedules
            ->filter(fn (SupplementSchedule $schedule) => !in_array($schedule->logs->first()?->status, ['taken', 'skipped'], true))
            ->map(fn (SupplementSchedule $schedule) => [
                ['text' => "Đã uống: {$schedule->name}", 'callback_data' => "supplement_schedule:{$schedule->id}:taken"],
                ['text' => "Bỏ qua: {$schedule->name}", 'callback_data' => "supplement_schedule:{$schedule->id}:skipped"],
            ])
            ->values()
            ->all();

        return $rows === [] ? [] : ['inline_keyboard' => $rows];
    }

    private function displayTime(?string $time): string
    {
        return filled($time) ? substr((string) $time, 0, 5) : 'Chưa có giờ';
    }

    private function displaySupplementTime(SupplementSchedule $schedule): string
    {
        if ($schedule->timing_type === 'fixed_time' && $schedule->scheduled_time) {
            return $this->displayTime($schedule->scheduled_time);
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

    private function supplementStatusLabel(?string $status): string
    {
        return match ($status) {
            'taken' => 'Đã uống',
            'skipped' => 'Bỏ qua',
            default => 'Chưa ghi nhận',
        };
    }

    private function mealStatusLabel(?string $status): string
    {
        return match ($status) {
            'done' => 'Đã ăn',
            'skipped' => 'Bỏ qua',
            default => 'Chưa ghi nhận',
        };
    }
}
