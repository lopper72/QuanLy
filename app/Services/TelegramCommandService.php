<?php

namespace App\Services;

use App\Models\Child;
use App\Models\MealLog;
use App\Models\MealPlanItem;
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

        return $this->handleCommand($chatId, $command, $argument);
    }

    public function handleCommand(string $chatId, string $command, string $argument = ''): bool
    {
        return match ($command) {
            'menu', 'help', 'lenh' => $this->sendMenu($chatId),
            'today', 'tap' => $this->sendTodayTraining($chatId),
            'full' => $this->sendFullSchedule($chatId),
            'thuoc' => $this->sendSupplements($chatId),
            'an' => $this->sendMeals($chatId),
            'doimon' => $this->sendAlternativeDinner($chatId),
            'tiendo' => $this->sendProgress($chatId),
            'ditoilet' => $this->sendToiletPrompt($chatId),
            'uongnuoc' => $this->sendWaterPrompt($chatId),
            'id' => $this->sendChatId($chatId),
            'hotro' => $this->sendSupportRequest($chatId, $argument),
            default => false,
        };
    }

    public function commands(): array
    {
        return [
            ['command' => 'menu', 'description' => 'Hiển thị menu hỗ trợ'],
            ['command' => 'an', 'description' => 'Xem lịch ăn uống hôm nay'],
            ['command' => 'doimon', 'description' => 'Gợi ý món tối thay thế'],
            ['command' => 'tap', 'description' => 'Xem lịch tập hôm nay'],
            ['command' => 'thuoc', 'description' => 'Xem lịch bổ sung hoặc thuốc'],
            ['command' => 'tiendo', 'description' => 'Xem tóm tắt hoạt động gần đây'],
            ['command' => 'ditoilet', 'description' => 'Ghi nhận tình trạng đi tiêu'],
            ['command' => 'uongnuoc', 'description' => 'Ghi nhận uống nước'],
            ['command' => 'full', 'description' => 'Xem toàn bộ lịch hôm nay'],
            ['command' => 'id', 'description' => 'Xem mã hội thoại Telegram'],
            ['command' => 'hotro', 'description' => 'Gửi yêu cầu hỗ trợ'],
        ];
    }

    public function handleMenuCallback(array $callback): bool
    {
        $chatId = $this->callbackChatId($callback);
        $data = (string) data_get($callback, 'data', '');

        if (blank($chatId) || !Str::startsWith($data, 'telegram_menu:')) {
            return false;
        }

        $this->logInboundCallback($callback, $chatId, 'menu_callback', $this->menuCallbackText($data), $data);

        return match ($data) {
            'telegram_menu:today_meal' => $this->sendMeals($chatId),
            'telegram_menu:today_training' => $this->sendTodayTraining($chatId),
            'telegram_menu:supplements' => $this->sendSupplements($chatId),
            'telegram_menu:change_meal' => $this->sendAlternativeDinner($chatId),
            default => false,
        };
    }

    public function handleToiletCallback(array $callback): bool
    {
        $chatId = $this->callbackChatId($callback);
        $data = (string) data_get($callback, 'data', '');

        if (blank($chatId) || !Str::startsWith($data, 'toilet:')) {
            return false;
        }

        $child = $this->firstActiveChildForChat($chatId);
        if (!$child) {
            $this->sendNoLinkedChildMessage($chatId);
            return true;
        }

        $status = Str::after($data, 'toilet:');
        $label = match ($status) {
            'soft' => 'Phân mềm, không đau.',
            'hard' => 'Phân cứng.',
            'pain' => 'Bé đau/rặn khi đi tiêu.',
            'none' => 'Hôm nay bé chưa đi tiêu.',
            default => 'Đã ghi nhận tình trạng đi tiêu.',
        };

        $this->dailyTrackingLog($child)->fill([
            'status' => 'noted',
            'stool_note' => $label,
            'notes' => 'Ghi nhận nhanh từ Telegram.',
        ])->save();

        $this->logInboundCallback($callback, $chatId, 'tracking_callback', "Phụ huynh đã ghi nhận đi tiêu: {$label}", $data, $child, $status);
        $this->telegramService->sendMessage($chatId, "✅ Đã ghi nhận:\n{$label}");

        return true;
    }

    public function handleWaterCallback(array $callback): bool
    {
        $chatId = $this->callbackChatId($callback);
        $data = (string) data_get($callback, 'data', '');

        if (blank($chatId) || !Str::startsWith($data, 'water:')) {
            return false;
        }

        $child = $this->firstActiveChildForChat($chatId);
        if (!$child) {
            $this->sendNoLinkedChildMessage($chatId);
            return true;
        }

        $status = Str::after($data, 'water:');
        $label = match ($status) {
            'good' => 'Hôm nay bé uống nước tốt.',
            'little' => 'Hôm nay bé uống nước ít.',
            'very_little' => 'Hôm nay bé uống rất ít nước.',
            default => 'Đã ghi nhận uống nước.',
        };

        $this->dailyTrackingLog($child)->fill([
            'status' => 'noted',
            'water_note' => $label,
            'notes' => 'Ghi nhận nhanh từ Telegram.',
        ])->save();

        $this->logInboundCallback($callback, $chatId, 'tracking_callback', "Phụ huynh đã ghi nhận uống nước: {$label}", $data, $child, $status);
        $this->telegramService->sendMessage($chatId, "💧 Đã ghi nhận:\n{$label}");

        return true;
    }

    private function parseCommand(string $text): array
    {
        $parts = preg_split('/\s+/', $text, 2);
        $rawCommand = ltrim((string) ($parts[0] ?? ''), '/');
        $command = Str::of($rawCommand)->before('@')->lower()->toString();

        return [$command, trim((string) ($parts[1] ?? ''))];
    }

    private function sendMenu(string $chatId): bool
    {
        $this->telegramService->sendMessage($chatId, implode("\n", [
            '🤖 Menu hỗ trợ phụ huynh',
            '',
            'Xin chào 👋',
            'Bạn có thể dùng các lệnh sau:',
            '',
            "📋 /an\nXem lịch ăn uống hôm nay",
            '',
            "🔁 /doimon\nNhận gợi ý món ăn khác cho bữa tối",
            '',
            "🏋 /tap\nXem lịch tập hôm nay",
            '',
            "💊 /thuoc\nXem lịch uống bổ sung / thuốc",
            '',
            "📊 /tiendo\nXem tóm tắt hoạt động gần đây",
            '',
            "🧻 /ditoilet\nGhi nhận tình trạng đi tiêu",
            '',
            "💧 /uongnuoc\nGhi nhận uống nước",
            '',
            "❓ /menu\nHiển thị menu hỗ trợ",
            '',
            '💡 Mẹo:',
            'Bạn cũng có thể bấm các nút nhanh bên dưới.',
        ]), [
            'inline_keyboard' => [
                [
                    ['text' => '📋 Lịch ăn hôm nay', 'callback_data' => 'telegram_menu:today_meal'],
                    ['text' => '🏋 Lịch tập hôm nay', 'callback_data' => 'telegram_menu:today_training'],
                ],
                [
                    ['text' => '💊 Lịch bổ sung', 'callback_data' => 'telegram_menu:supplements'],
                    ['text' => '🔁 Đổi món tối nay', 'callback_data' => 'telegram_menu:change_meal'],
                ],
            ],
        ]);

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
            $this->telegramService->sendMessage($chatId, $this->buildFullScheduleMessage($child));
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
            $this->telegramService->sendMessage($chatId, $this->buildSupplementMessage($child), $this->supplementKeyboard($child));
        }

        return true;
    }

    private function sendMeals(string $chatId): bool
    {
        $children = $this->mealSuggestionService->activeChildrenForChat($chatId);

        if ($children->isEmpty()) {
            $this->sendNoLinkedChildMessage($chatId);
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
            $this->sendNoLinkedChildMessage($chatId);
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

    private function sendToiletPrompt(string $chatId): bool
    {
        $this->telegramService->sendMessage($chatId, implode("\n", [
            '🧻 Ghi nhận đi tiêu hôm nay',
            '',
            'Vui lòng chọn tình trạng phù hợp:',
        ]), [
            'inline_keyboard' => [
                [
                    ['text' => '🙂 Phân mềm', 'callback_data' => 'toilet:soft'],
                    ['text' => '😣 Phân cứng', 'callback_data' => 'toilet:hard'],
                ],
                [
                    ['text' => '⚠ Đau/rặn', 'callback_data' => 'toilet:pain'],
                    ['text' => '❌ Chưa đi', 'callback_data' => 'toilet:none'],
                ],
            ],
        ]);

        return true;
    }

    private function sendWaterPrompt(string $chatId): bool
    {
        $this->telegramService->sendMessage($chatId, '💧 Hôm nay bé uống nước thế nào?', [
            'inline_keyboard' => [
                [
                    ['text' => '👍 Uống tốt', 'callback_data' => 'water:good'],
                    ['text' => '😐 Uống ít', 'callback_data' => 'water:little'],
                ],
                [
                    ['text' => '👎 Uống rất ít', 'callback_data' => 'water:very_little'],
                ],
            ],
        ]);

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

        $this->telegramService->sendMessage($chatId, 'Đã ghi nhận yêu cầu hỗ trợ. Người phụ trách sẽ xem lại và phản hồi khi có thể.');

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

    private function firstActiveChildForChat(string $chatId): ?Child
    {
        return $this->mealSuggestionService->activeChildrenForChat($chatId)->first();
    }

    private function buildFullScheduleMessage(Child $child): string
    {
        return implode("\n", [
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
        ]);
    }

    private function buildSupplementMessage(Child $child): string
    {
        return implode("\n", [
            "💊 Lịch bổ sung hôm nay của bé {$child->full_name}",
            '',
            ...$this->supplementLines($child, true),
            '',
            'Lưu ý:',
            'Liều dùng theo hướng dẫn của bác sĩ hoặc nhãn sản phẩm.',
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
        $mealTotal = max($mealLogs->count(), $this->todayMealItems()->count());
        $latestTracking = MealLog::where('child_id', $child->id)->whereNotNull('stool_note')->latest('meal_date')->latest('id')->first();

        return implode("\n", [
            '📊 Tóm tắt gần đây',
            '',
            "Bé: {$child->full_name}",
            '',
            "🏋 Bài tập: {$trainingCompleted}/{$trainingTotal} đã hoàn thành hôm nay",
            "💊 Bổ sung: {$supplementTaken}/{$supplementTotal} đã uống, {$supplementSkipped} bỏ qua",
            "🍽 Bữa ăn đã ghi nhận: {$mealsDone}/{$mealTotal}",
            '🧻 Ghi nhận đi tiêu gần nhất:',
            $latestTracking?->stool_note ? "- {$latestTracking->stool_note}" : '- Chưa có ghi nhận',
            '',
            '💡 Duy trì đều giúp dễ theo dõi tiến triển hơn.',
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
                $note = $schedule->dosage_note ? "\nGhi chú: {$schedule->dosage_note}" : '';

                return "{$this->displaySupplementTime($schedule)} - {$schedule->name}{$status}{$note}";
            })
            ->all();
    }

    private function mealLines(Child $child): array
    {
        $logs = $child->mealLogs;

        if ($logs->isNotEmpty()) {
            return $logs->map(function (MealLog $log) {
                $item = $log->item;
                $time = $log->scheduled_for ? $log->scheduled_for->format('H:i') : $this->displayTime($item?->scheduled_time);
                $foods = collect($item?->foods_json ?? [])->implode(', ');

                return "{$time} - ".($item?->title ?: 'Bữa ăn đã ghi nhận').($foods ? ": {$foods}" : '');
            })->all();
        }

        $items = $this->todayMealItems();
        if ($items->isEmpty()) {
            return ['Chưa có lịch ăn hôm nay.'];
        }

        return $items->map(function (MealPlanItem $item) {
            $foods = collect($item->foods_json ?? [])->implode(', ');

            return "{$this->displayTime($item->scheduled_time)} - {$item->title}".($foods ? ": {$foods}" : '');
        })->all();
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

    private function dailyTrackingLog(Child $child): MealLog
    {
        return MealLog::query()
            ->where('child_id', $child->id)
            ->whereDate('meal_date', today())
            ->whereNull('meal_plan_item_id')
            ->first()
            ?? new MealLog([
                'child_id' => $child->id,
                'meal_date' => today()->toDateString(),
                'meal_plan_item_id' => null,
            ]);
    }

    private function logInboundCallback(array $callback, string $chatId, string $type, string $text, string $data, ?Child $child = null, ?string $status = null): void
    {
        TelegramMessage::create([
            'direction' => TelegramMessage::DIRECTION_INBOUND,
            'telegram_chat_id' => $chatId,
            'telegram_user_id' => (string) data_get($callback, 'from.id', ''),
            'telegram_username' => data_get($callback, 'from.username'),
            'message_type' => $type,
            'message_text' => $text,
            'callback_data' => $data,
            'action_status' => $status ?? Str::after($data, ':'),
            'payload_json' => ['callback' => $callback],
            'delivery_status' => TelegramMessage::STATUS_RECEIVED,
            'received_at' => now(),
            'related_child_id' => $child?->id,
        ]);
    }

    private function menuCallbackText(string $data): string
    {
        return match ($data) {
            'telegram_menu:today_meal' => 'Phụ huynh đã bấm: Lịch ăn hôm nay',
            'telegram_menu:today_training' => 'Phụ huynh đã bấm: Lịch tập hôm nay',
            'telegram_menu:supplements' => 'Phụ huynh đã bấm: Lịch bổ sung',
            'telegram_menu:change_meal' => 'Phụ huynh đã bấm: Đổi món tối nay',
            default => 'Phụ huynh đã bấm menu Telegram',
        };
    }

    private function callbackChatId(array $callback): string
    {
        return (string) data_get($callback, 'message.chat.id', data_get($callback, 'from.id', ''));
    }

    private function sendNoLinkedChildMessage(string $chatId): void
    {
        $this->telegramService->sendMessage($chatId, 'Chưa liên kết Telegram với hồ sơ trẻ. Vui lòng liên kết trước khi sử dụng lệnh này.');
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
}
