<?php

namespace App\Services;

use App\Models\Child;
use App\Models\MealLog;
use App\Models\MealPlanItem;
use App\Models\TelegramMealSuggestionLog;
use App\Models\TelegramMessage;
use App\Models\TelegramSetting;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TelegramMealSuggestionService
{
    private const SAFETY_NOTE = 'Nếu bé táo bón kéo dài, đau bụng nhiều, đi ngoài ra máu, nôn, sụt cân hoặc nhiều ngày không đi tiêu, cần liên hệ bác sĩ.';

    private const ALTERNATIVE_MENUS = [
        [
            'Cháo yến mạch mềm',
            'Rau mồng tơi nấu thịt bằm',
            'Khoai lang hấp',
            'Đu đủ chín',
        ],
        [
            'Cơm mềm',
            'Canh bí đỏ',
            'Cá hấp',
            'Thanh long chín',
        ],
        [
            'Cháo đậu xanh lượng nhỏ',
            'Rau củ mềm',
            'Trứng hấp',
            'Chuối chín',
        ],
        [
            'Súp khoai lang',
            'Rau xanh nấu mềm',
            'Thịt bằm mềm',
            'Sữa chua không đường nếu bé dung nạp tốt',
        ],
    ];

    public function __construct(private readonly TelegramService $telegramService)
    {
    }

    public function sendDailyDinnerSuggestions(?Carbon $date = null): array
    {
        $date ??= today();
        $result = ['children' => 0, 'sent' => 0, 'skipped' => 0, 'failed' => 0];

        Child::active()
            ->orderBy('full_name')
            ->get()
            ->each(function (Child $child) use ($date, &$result) {
                $result['children']++;
                $log = $this->sendDinnerSuggestionForChild($child, $date);

                if (!$log) {
                    $result['skipped']++;
                    return;
                }

                if ($log->status === TelegramMealSuggestionLog::STATUS_SENT) {
                    $result['sent']++;
                    return;
                }

                if ($log->status === TelegramMealSuggestionLog::STATUS_FAILED) {
                    $result['failed']++;
                    return;
                }

                $result['skipped']++;
            });

        return $result;
    }

    public function sendDinnerSuggestionForChild(Child $child, ?Carbon $date = null, ?string $chatId = null): ?TelegramMealSuggestionLog
    {
        $date ??= today();

        if (!$child->isActive()) {
            return null;
        }

        $chatId ??= $this->resolveChatId($child);
        if (blank($chatId)) {
            return null;
        }

        $plan = $this->getTodayDinnerPlan($child, $date);
        $messageText = $this->buildDinnerSuggestionMessage($child, $date);
        $suggestionDate = $date->toDateString();
        $log = TelegramMealSuggestionLog::query()
            ->where('child_id', $child->id)
            ->where('telegram_chat_id', (string) $chatId)
            ->whereDate('suggestion_date', $suggestionDate)
            ->first();

        if (!$log) {
            $log = TelegramMealSuggestionLog::create([
                'child_id' => $child->id,
                'suggestion_date' => $suggestionDate,
                'telegram_chat_id' => (string) $chatId,
                'meal_plan_item_id' => $plan?->id,
                'message_text' => $messageText,
                'status' => TelegramMealSuggestionLog::STATUS_PENDING,
            ]);
        }

        if (in_array($log->status, [TelegramMealSuggestionLog::STATUS_SENT, TelegramMealSuggestionLog::STATUS_PREPARED], true)) {
            return $log;
        }

        $message = $this->telegramService->logOutboundMessage((string) $chatId, $messageText, [
            'message_type' => 'meal_suggestion',
            'payload_json' => [
                'reply_markup' => $this->suggestionKeyboard($child, $date),
                'meal_suggestion_log_id' => $log->id,
            ],
            'related_child_id' => $child->id,
        ]);
        $response = $this->telegramService->deliverLoggedMessage($message, $this->suggestionKeyboard($child, $date));

        $log->update([
            'message_text' => $messageText,
            'sent_at' => now(),
            'status' => $response?->successful()
                ? TelegramMealSuggestionLog::STATUS_SENT
                : TelegramMealSuggestionLog::STATUS_FAILED,
            'error_message' => $response?->successful() ? null : 'Không gửi được tin nhắn Telegram.',
        ]);

        return $log->refresh();
    }

    public function buildDinnerSuggestionMessage(Child $child, Carbon $date): string
    {
        $items = $this->dinnerFoods($child, $date);

        return implode("\n", [
            "🍽 Gợi ý bữa tối hôm nay cho bé {$child->full_name}",
            '',
            '🕒 Gửi lúc: 14:00',
            '📅 Ngày: '.$date->format('d/m/Y'),
            '',
            'Tối nay có thể thử:',
            ...$this->numberedLines($items),
            '',
            '🎯 Mục tiêu:',
            'Có thể hỗ trợ tiêu hóa, tăng nước và tạo thói quen ăn uống đều hơn nếu phù hợp với bé.',
            '',
            '💡 Vì sao nên đổi món?',
            'Thay đổi món nhẹ nhàng giúp bé:',
            '- bớt nhàm chán',
            '- tăng trải nghiệm vị giác',
            '- làm quen món mới từng chút một',
            '- hỗ trợ hệ tiêu hóa đa dạng hơn',
            '',
            'Lưu ý:',
            self::SAFETY_NOTE,
        ]);
    }

    public function getTodayDinnerPlan(Child $child, Carbon $date): ?MealPlanItem
    {
        $logItem = MealLog::with('item.template')
            ->where('child_id', $child->id)
            ->whereDate('meal_date', $date)
            ->whereHas('item', fn ($query) => $query->where('meal_time', 'dinner'))
            ->latest('id')
            ->first()
            ?->item;

        if ($logItem) {
            return $logItem;
        }

        return MealPlanItem::with('template')
            ->where('day_of_week', $date->dayOfWeekIso)
            ->where('meal_time', 'dinner')
            ->whereHas('template', fn ($query) => $query->active())
            ->orderBy('scheduled_time')
            ->first();
    }

    public function suggestAlternativeDinner(Child $child, ?Carbon $date = null): array
    {
        $date ??= today();
        $current = $this->dinnerFoods($child, $date);
        $currentKey = $this->menuKey($current);

        return collect(self::ALTERNATIVE_MENUS)
            ->first(fn (array $menu) => $this->menuKey($menu) !== $currentKey)
            ?? self::ALTERNATIVE_MENUS[0];
    }

    public function sendAlternativeDinnerForChat(string $chatId, Child $child, ?Carbon $date = null): void
    {
        $date ??= today();
        $menu = $this->suggestAlternativeDinner($child, $date);

        $this->telegramService->sendMessage($chatId, implode("\n", [
            '🔁 Gợi ý món thay thế cho tối nay',
            '',
            "Bé: {$child->full_name}",
            '',
            'Bạn có thể đổi sang:',
            ...$this->numberedLines($menu),
            '',
            '🎯 Mục tiêu:',
            'Tăng chất xơ nhẹ nhàng và có thể hỗ trợ làm mềm phân nếu phù hợp với bé.',
            '',
            '💡 Mẹo:',
            'Chỉ đổi 1-2 món nhỏ, không cần đổi toàn bộ bữa ăn để bé dễ chấp nhận hơn.',
        ]), $this->suggestionKeyboard($child, $date));
    }

    public function sendTodayMealScheduleForChat(string $chatId, Child $child, ?Carbon $date = null): void
    {
        $date ??= today();
        $this->telegramService->sendMessage(
            $chatId,
            $this->buildTodayMealScheduleMessage($child, $date),
            $this->mealScheduleKeyboard($child, $date)
        );
    }

    public function buildTodayMealScheduleMessage(Child $child, Carbon $date): string
    {
        $lines = [
            "📋 Lịch ăn uống hôm nay của bé {$child->full_name}",
            '',
            '📅 Ngày: '.$date->format('d/m/Y'),
            '',
            '14:00 - Nhận gợi ý bữa tối',
        ];

        $mealLines = $this->todayMealLines($child, $date);
        array_push($lines, ...$mealLines);

        $lines[] = '';
        $lines[] = '20:30 - Gợi ý thói quen';
        $lines[] = 'Cho bé ngồi toilet 5 phút sau bữa tối nếu phù hợp.';
        $lines[] = '';
        $lines[] = '💧 Nhắc uống nước:';
        $lines[] = 'Chia nhỏ nước trong ngày, không ép uống quá nhiều một lúc.';

        return implode("\n", $lines);
    }

    public function handleCallback(array $callback): ?TelegramMessage
    {
        $data = (string) ($callback['data'] ?? '');
        if (!preg_match('/^meal_suggestion:(\d{1,10}):(\d{4}-\d{2}-\d{2}):(change|view|prepared)$/', $data, $matches)) {
            return null;
        }

        $child = Child::active()->find((int) $matches[1]);
        if (!$child) {
            return null;
        }

        $date = Carbon::parse($matches[2]);
        $action = $matches[3];
        $message = $callback['message'] ?? [];
        $chat = $message['chat'] ?? [];
        $from = $callback['from'] ?? [];
        $chatId = (string) ($chat['id'] ?? $from['id'] ?? '');

        $inbound = TelegramMessage::create([
            'direction' => TelegramMessage::DIRECTION_INBOUND,
            'telegram_chat_id' => $chatId,
            'telegram_user_id' => isset($from['id']) ? (string) $from['id'] : null,
            'telegram_username' => $from['username'] ?? $chat['username'] ?? null,
            'message_type' => 'meal_suggestion_callback',
            'message_text' => $this->callbackLogText($action),
            'callback_data' => $data,
            'action_status' => $action,
            'payload_json' => ['callback' => $callback],
            'delivery_status' => TelegramMessage::STATUS_RECEIVED,
            'received_at' => now(),
            'related_child_id' => $child->id,
        ]);

        if (blank($chatId)) {
            return $inbound;
        }

        if ($action === 'change') {
            $this->sendAlternativeDinnerForChat($chatId, $child, $date);
        }

        if ($action === 'view') {
            $this->sendTodayMealScheduleForChat($chatId, $child, $date);
        }

        if ($action === 'prepared') {
            TelegramMealSuggestionLog::updateOrCreate(
                [
                    'child_id' => $child->id,
                    'telegram_chat_id' => $chatId,
                    'suggestion_date' => $date->toDateString(),
                ],
                [
                    'meal_plan_item_id' => $this->getTodayDinnerPlan($child, $date)?->id,
                    'message_text' => $this->buildDinnerSuggestionMessage($child, $date),
                    'status' => TelegramMealSuggestionLog::STATUS_PREPARED,
                    'sent_at' => now(),
                    'error_message' => null,
                ]
            );

            $this->telegramService->sendMessage($chatId, '✅ Đã ghi nhận: Phụ huynh đã chuẩn bị bữa tối.');
        }

        return $inbound;
    }

    public function callbackFeedbackText(string $action): string
    {
        return match ($action) {
            'change' => 'Đang gợi ý món khác',
            'view' => 'Đang mở lịch hôm nay',
            'prepared' => 'Đã ghi nhận chuẩn bị bữa tối',
            default => 'Đã ghi nhận phản hồi',
        };
    }

    public function activeChildrenForChat(string $chatId): Collection
    {
        if (!$this->chatIsLinked($chatId)) {
            return collect();
        }

        return Child::active()->orderBy('full_name')->get();
    }

    public function previewPayload(?Child $child = null): array
    {
        $child ??= Child::active()->orderBy('full_name')->first();

        return [
            'preview_message' => $child ? $this->buildDinnerSuggestionMessage($child, today()) : 'Chưa có trẻ đang can thiệp.',
            'alternative_message' => $child ? implode("\n", $this->numberedLines($this->suggestAlternativeDinner($child, today()))) : '',
            'today_message' => $child ? $this->buildTodayMealScheduleMessage($child, today()) : '',
        ];
    }

    public function resolveChatId(?Child $child = null): ?string
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

    private function chatIsLinked(string $chatId): bool
    {
        $settings = TelegramSetting::current();
        if (filled($settings->default_chat_id) && (string) $settings->default_chat_id === $chatId) {
            return true;
        }

        return User::query()
            ->where('telegram_chat_id', $chatId)
            ->where('telegram_notifications_enabled', true)
            ->exists();
    }

    private function dinnerFoods(Child $child, Carbon $date): array
    {
        $plan = $this->getTodayDinnerPlan($child, $date);
        $foods = collect($plan?->foods_json ?? [])->filter()->values()->all();

        return $foods !== [] ? $foods : self::ALTERNATIVE_MENUS[1];
    }

    private function todayMealLines(Child $child, Carbon $date): array
    {
        $logs = MealLog::with('item.template')
            ->where('child_id', $child->id)
            ->whereDate('meal_date', $date)
            ->orderBy('scheduled_for')
            ->get();

        if ($logs->isNotEmpty()) {
            return $logs->map(function (MealLog $log) {
                $time = $log->scheduled_for ? $log->scheduled_for->format('H:i') : substr((string) $log->item?->scheduled_time, 0, 5);
                $foods = collect($log->item?->foods_json ?? [])->map(fn ($food) => "- {$food}")->implode("\n");

                return trim("{$time} - ".($log->item?->title ?: 'Bữa ăn')."\n{$foods}");
            })->all();
        }

        $items = MealPlanItem::with('template')
            ->where('day_of_week', $date->dayOfWeekIso)
            ->whereHas('template', fn ($query) => $query->active())
            ->orderBy('scheduled_time')
            ->orderBy('meal_time')
            ->get();

        if ($items->isEmpty()) {
            return ['Chưa có lịch ăn hôm nay.'];
        }

        return $items->map(function (MealPlanItem $item) {
            $time = $item->scheduled_time ? substr((string) $item->scheduled_time, 0, 5) : 'Chưa có giờ';
            $foods = collect($item->foods_json ?? [])->map(fn ($food) => "- {$food}")->implode("\n");

            return trim("{$time} - {$item->title}\n{$foods}");
        })->all();
    }

    private function numberedLines(array $items): array
    {
        return collect($items)
            ->values()
            ->map(fn ($item, int $index) => ($index + 1).'. '.$item)
            ->all();
    }

    private function suggestionKeyboard(Child $child, Carbon $date): array
    {
        $dateValue = $date->toDateString();

        return [
            'inline_keyboard' => [
                [
                    ['text' => '🔁 Đổi món khác', 'callback_data' => "meal_suggestion:{$child->id}:{$dateValue}:change"],
                    ['text' => '📋 Xem lịch hôm nay', 'callback_data' => "meal_suggestion:{$child->id}:{$dateValue}:view"],
                ],
                [
                    ['text' => '✅ Đã chuẩn bị', 'callback_data' => "meal_suggestion:{$child->id}:{$dateValue}:prepared"],
                ],
            ],
        ];
    }

    private function mealScheduleKeyboard(Child $child, Carbon $date): array
    {
        $dateValue = $date->toDateString();

        return [
            'inline_keyboard' => [
                [
                    ['text' => '🔁 Đổi món khác', 'callback_data' => "meal_suggestion:{$child->id}:{$dateValue}:change"],
                    ['text' => '✅ Đã chuẩn bị', 'callback_data' => "meal_suggestion:{$child->id}:{$dateValue}:prepared"],
                ],
            ],
        ];
    }

    private function callbackLogText(string $action): string
    {
        return match ($action) {
            'change' => 'Phụ huynh đã bấm: Đổi món khác',
            'view' => 'Phụ huynh đã bấm: Xem lịch hôm nay',
            'prepared' => 'Phụ huynh đã bấm: Đã chuẩn bị',
            default => 'Phụ huynh đã bấm nút gợi ý bữa ăn',
        };
    }

    private function menuKey(array $items): string
    {
        return collect($items)->map(fn ($item) => mb_strtolower(trim((string) $item)))->implode('|');
    }
}
