<?php

namespace App\Services;

use App\Jobs\SendTelegramMessageJob;
use App\Models\ChecklistItem;
use App\Models\DailyChecklist;
use App\Models\TelegramContact;
use App\Models\TelegramMessage;
use App\Models\TelegramSetting;
use App\Models\User;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramService
{
    public function ensureLinkToken(User $user): string
    {
        if (filled($user->telegram_link_token)) {
            return $user->telegram_link_token;
        }

        do {
            $token = Str::random(40);
        } while (User::where('telegram_link_token', $token)->exists());

        $user->forceFill(['telegram_link_token' => $token])->save();

        return $token;
    }

    public function linkUrl(User $user): string
    {
        $token = $this->ensureLinkToken($user);
        $botUsername = trim((string) $this->botUsername(), '@');

        return "https://t.me/{$botUsername}?start=parent_{$token}";
    }

    public function linkParent(string $token, string|int $chatId): ?User
    {
        $user = User::where('telegram_link_token', $token)->first();

        if (!$user) {
            return null;
        }

        $user->forceFill([
            'telegram_chat_id' => (string) $chatId,
            'telegram_notifications_enabled' => true,
        ])->save();

        return $user;
    }

    public function sendMessage(string|int $chatId, string $text, array $replyMarkup = []): ?Response
    {
        $message = $this->logOutboundMessage((string) $chatId, $text, [
            'message_type' => 'text',
            'payload_json' => ['reply_markup' => $replyMarkup],
        ]);

        return $this->deliverLoggedMessage($message, $replyMarkup);
    }

    public function sendTestMessage(string|int|null $chatId, string $text): TelegramMessage
    {
        $targetChatId = filled($chatId) ? (string) $chatId : (string) TelegramSetting::current()->default_chat_id;

        $message = $this->logOutboundMessage($targetChatId, $text, [
            'message_type' => 'test',
            'payload_json' => ['source' => 'test_panel'],
        ]);

        $this->deliverLoggedMessage($message);

        return $message->refresh();
    }

    public function processWebhook(array $payload): ?TelegramMessage
    {
        if (isset($payload['message'])) {
            return $this->logInboundMessage($payload['message'], $payload);
        }

        if (isset($payload['callback_query'])) {
            $callback = $payload['callback_query'];
            if (str_starts_with((string) ($callback['data'] ?? ''), 'training_session:')) {
                return null;
            }

            $message = $callback['message'] ?? [];

            return $this->logInboundMessage([
                'chat' => $message['chat'] ?? [],
                'from' => $callback['from'] ?? [],
                'text' => $callback['data'] ?? '',
                'date' => $message['date'] ?? now()->timestamp,
            ], $payload, 'callback');
        }

        return null;
    }

    public function logInboundMessage(array $message, array $payload = [], string $messageType = 'text'): TelegramMessage
    {
        $chat = $message['chat'] ?? [];
        $from = $message['from'] ?? [];
        $chatId = (string) ($chat['id'] ?? $from['id'] ?? '');
        $receivedAt = isset($message['date'])
            ? Carbon::createFromTimestamp((int) $message['date'])
            : now();

        $this->syncTelegramContact($chat, $from, $receivedAt);

        return TelegramMessage::create([
            'direction' => TelegramMessage::DIRECTION_INBOUND,
            'telegram_chat_id' => $chatId,
            'telegram_user_id' => isset($from['id']) ? (string) $from['id'] : null,
            'telegram_username' => $from['username'] ?? $chat['username'] ?? null,
            'message_type' => $messageType,
            'message_text' => $message['text'] ?? $message['caption'] ?? '',
            'payload_json' => $payload ?: $message,
            'delivery_status' => TelegramMessage::STATUS_RECEIVED,
            'received_at' => $receivedAt,
        ]);
    }

    public function logOutboundMessage(string $chatId, string $text, array $attributes = []): TelegramMessage
    {
        $this->syncTelegramContact(['id' => $chatId], [], now());

        return TelegramMessage::create(array_merge([
            'direction' => TelegramMessage::DIRECTION_OUTBOUND,
            'telegram_chat_id' => $chatId,
            'message_type' => 'text',
            'message_text' => $text,
            'payload_json' => [],
            'delivery_status' => TelegramMessage::STATUS_PENDING,
        ], $attributes));
    }

    public function syncTelegramContact(array $chat = [], array $from = [], ?Carbon $seenAt = null): ?TelegramContact
    {
        $chatId = $chat['id'] ?? $from['id'] ?? null;

        if (blank($chatId)) {
            return null;
        }

        $firstName = trim((string) ($from['first_name'] ?? $chat['first_name'] ?? ''));
        $lastName = trim((string) ($from['last_name'] ?? $chat['last_name'] ?? ''));
        $displayName = trim("{$firstName} {$lastName}") ?: ($chat['title'] ?? null);

        $values = [
            'last_seen_at' => $seenAt ?? now(),
            'is_active' => true,
        ];

        if (isset($from['id'])) {
            $values['telegram_user_id'] = (string) $from['id'];
        }

        if (isset($from['username']) || isset($chat['username'])) {
            $values['telegram_username'] = $from['username'] ?? $chat['username'];
        }

        if ($displayName !== '') {
            $values['display_name'] = $displayName;
        }

        return TelegramContact::updateOrCreate(['telegram_chat_id' => (string) $chatId], $values);
    }

    public function answerCallbackQuery(string $callbackQueryId, string $text = ''): ?Response
    {
        $token = $this->botToken();
        if (blank($token) || blank($callbackQueryId)) {
            Log::info('Telegram answerCallbackQuery skipped', [
                'has_token' => filled($token),
                'has_callback_query_id' => filled($callbackQueryId),
            ]);

            return null;
        }

        $response = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/answerCallbackQuery", [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => false,
        ]);

        Log::info('Telegram answerCallbackQuery response', [
            'callback_query_id' => $callbackQueryId,
            'successful' => $response->successful(),
            'status' => $response->status(),
            'response' => $response->json(),
        ]);

        return $response;
    }

    public function getWebhookInfo(): ?Response
    {
        $token = $this->botToken();
        if (blank($token)) {
            return null;
        }

        return Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getWebhookInfo");
    }

    public function setWebhook(?string $url = null, ?string $secret = null): ?Response
    {
        $token = $this->botToken();
        $url ??= $this->webhookUrl();
        $secret ??= $this->webhookSecret();

        if (blank($token) || blank($url)) {
            Log::info('Telegram setWebhook skipped', [
                'has_token' => filled($token),
                'has_url' => filled($url),
            ]);

            return null;
        }

        $payload = [
            'url' => $url,
            'allowed_updates' => ['message', 'callback_query'],
            'drop_pending_updates' => false,
        ];

        if (filled($secret)) {
            $payload['secret_token'] = $secret;
        }

        $response = Http::timeout(15)->post("https://api.telegram.org/bot{$token}/setWebhook", $payload);

        Log::info('Telegram setWebhook response', [
            'webhook_url' => $url,
            'successful' => $response->successful(),
            'status' => $response->status(),
            'response' => $response->json(),
        ]);

        return $response;
    }

    public function deleteWebhook(bool $dropPendingUpdates = false): ?Response
    {
        $token = $this->botToken();
        if (blank($token)) {
            return null;
        }

        $response = Http::timeout(15)->post("https://api.telegram.org/bot{$token}/deleteWebhook", [
            'drop_pending_updates' => $dropPendingUpdates,
        ]);

        Log::info('Telegram deleteWebhook response', [
            'drop_pending_updates' => $dropPendingUpdates,
            'successful' => $response->successful(),
            'status' => $response->status(),
            'response' => $response->json(),
        ]);

        return $response;
    }

    public function setMyCommands(array $commands): ?Response
    {
        $token = $this->botToken();
        if (blank($token)) {
            return null;
        }

        $response = Http::timeout(15)->post("https://api.telegram.org/bot{$token}/setMyCommands", [
            'commands' => $commands,
        ]);

        Log::info('Telegram setMyCommands response', [
            'successful' => $response->successful(),
            'status' => $response->status(),
            'response' => $response->json(),
        ]);

        return $response;
    }

    public function editMessageReplyMarkup(string|int $chatId, string|int $messageId, array $replyMarkup = []): ?Response
    {
        $token = $this->botToken();
        if (blank($token) || blank($chatId) || blank($messageId)) {
            return null;
        }

        $response = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/editMessageReplyMarkup", [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'reply_markup' => $replyMarkup,
        ]);

        Log::info('Telegram editMessageReplyMarkup response', [
            'chat_id' => (string) $chatId,
            'message_id' => (string) $messageId,
            'successful' => $response->successful(),
            'status' => $response->status(),
            'response' => $response->json(),
        ]);

        return $response;
    }

    public function queueChecklistReminder(ChecklistItem $item, User $user): void
    {
        $item->loadMissing(['dailyChecklist.child', 'trainingSessionItem.exercise', 'trainingSessionItem.trainingSession']);

        if (blank($user->telegram_chat_id) || !$user->telegram_notifications_enabled) {
            return;
        }

        $exerciseTitle = $item->trainingSessionItem?->exercise?->title ?: 'bài tập';
        $time = $item->trainingSessionItem?->trainingSession?->scheduled_time;
        $prefix = $time ? "⏰ 15 phút nữa tới bài tập:" : '⏰ Nhắc lịch bài tập:';

        SendTelegramMessageJob::dispatch(
            $user->telegram_chat_id,
            "{$prefix} {$exerciseTitle}",
            $this->checklistInlineKeyboard($item, $user)
        );
    }

    public function queueMorningChecklist(User $user, DailyChecklist $checklist): void
    {
        $count = $checklist->items->count();
        $childName = $checklist->child->full_name;

        if (blank($user->telegram_chat_id) || !$user->telegram_notifications_enabled || $count < 1) {
            return;
        }

        SendTelegramMessageJob::dispatch(
            $user->telegram_chat_id,
            "🧩 Hôm nay có {$count} bài tập cho {$childName}",
            $this->openChecklistKeyboard($user)
        );

        foreach ($checklist->items as $item) {
            $this->queueChecklistItem($user, $item);
        }
    }

    public function queueChecklistItem(User $user, ChecklistItem $item): void
    {
        if (blank($user->telegram_chat_id) || !$user->telegram_notifications_enabled) {
            return;
        }

        $item->loadMissing(['trainingSessionItem.exercise', 'trainingSessionItem.trainingSession']);
        $exerciseName = $item->trainingSessionItem?->exercise?->title ?: 'Bài tập';
        $time = $item->trainingSessionItem?->trainingSession?->scheduled_time ?: '--:--';
        $duration = $item->trainingSessionItem?->duration_minutes ?: 0;

        $text = "⏰ {$time} • {$exerciseName}\n⏱ {$duration} phút";

        SendTelegramMessageJob::dispatch(
            $user->telegram_chat_id,
            $text,
            $this->checklistInlineKeyboard($item, $user)
        );
    }

    public function queueEndOfDayReport(User $user, string $childName, int $completed, int $total, ?int $unfinished = null): void
    {
        if (blank($user->telegram_chat_id) || !$user->telegram_notifications_enabled || $total < 1) {
            return;
        }

        $unfinished ??= max($total - $completed, 0);

        SendTelegramMessageJob::dispatch(
            $user->telegram_chat_id,
            "🌙 Tổng kết hôm nay của {$childName}: hoàn thành {$completed}/{$total} bài tập. Bạn còn {$unfinished} buổi chưa hoàn thành hôm nay.",
            $this->openChecklistKeyboard($user)
        );
    }

    public function updateChecklistFromCallback(string $data): ?ChecklistItem
    {
        [$action, $id] = array_pad(explode(':', $data, 2), 2, null);

        $validActions = ['complete', 'refused', 'checklist_done', 'checklist_refuse'];
        if (!in_array($action, $validActions, true) || !ctype_digit((string) $id)) {
            return null;
        }

        $item = ChecklistItem::with(['dailyChecklist.child', 'trainingSessionItem.exercise'])->find((int) $id);
        if (!$item) {
            return null;
        }

        $service = app(DailyChecklistService::class);
        $isDone = in_array($action, ['complete', 'checklist_done'], true);

        return $service->updateItem($item, [
            'status' => $isDone
                ? ChecklistItem::STATUS_COMPLETED
                : ChecklistItem::STATUS_REFUSED,
            'performance_result' => $isDone ? 'good' : 'not_cooperative',
        ]);
    }

    public function checklistInlineKeyboard(ChecklistItem $item, ?User $user = null): array
    {
        $url = $user
            ? \Illuminate\Support\Facades\URL::temporarySignedRoute('telegram.login', now()->addHours(12), ['user' => $user->id])
            : url('/today');

        return [
            'inline_keyboard' => [
                [
                    ['text' => '✅ Đã tập', 'callback_data' => "checklist_done:{$item->id}"],
                    ['text' => '❌ Bé từ chối', 'callback_data' => "checklist_refuse:{$item->id}"],
                ],
                [
                    ['text' => '📝 Ghi chú', 'callback_data' => "checklist_note:{$item->id}"],
                    ['text' => '🌐 Mở checklist', 'url' => $url],
                ],
            ],
        ];
    }

    public function openChecklistKeyboard(?User $user = null): array
    {
        $url = $user
            ? \Illuminate\Support\Facades\URL::temporarySignedRoute('telegram.login', now()->addHours(12), ['user' => $user->id])
            : url('/today');

        return [
            'inline_keyboard' => [
                [
                    ['text' => 'Mở checklist', 'url' => $url],
                ],
            ],
        ];
    }

    public function botToken(): ?string
    {
        $setting = TelegramSetting::query()->first();

        return $setting?->bot_token ?: config('services.telegram.bot_token');
    }

    public function botUsername(): string
    {
        $setting = TelegramSetting::query()->first();

        return $setting?->bot_username ?: config('services.telegram.bot_username', 'YOUR_BOT');
    }

    public function webhookSecret(): ?string
    {
        $setting = TelegramSetting::query()->first();

        return $setting?->webhook_secret ?: config('services.telegram.webhook_secret');
    }

    public function webhookUrl(): ?string
    {
        $setting = TelegramSetting::query()->first();

        return $setting?->webhook_url ?: config('services.telegram.webhook_url');
    }

    public function deliverLoggedMessage(TelegramMessage $message, array $replyMarkup = []): ?Response
    {
        $token = $this->botToken();

        if (blank($token) || blank($message->telegram_chat_id)) {
            $message->update([
                'delivery_status' => TelegramMessage::STATUS_FAILED,
                'sent_at' => now(),
            ]);

            return null;
        }

        $payload = [
            'chat_id' => $message->telegram_chat_id,
            'text' => $message->message_text,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        if ($replyMarkup !== []) {
            $payload['reply_markup'] = $replyMarkup;
        }

        try {
            $response = Http::timeout(10)->retry(2, 250, throw: false)->post("https://api.telegram.org/bot{$token}/sendMessage", $payload);
        } catch (\Throwable $exception) {
            Log::info('Telegram sendMessage failed before response', [
                'message_id' => $message->id,
                'chat_id' => $message->telegram_chat_id,
                'error' => $exception->getMessage(),
            ]);

            $message->update([
                'delivery_status' => TelegramMessage::STATUS_FAILED,
                'payload_json' => array_merge($message->payload_json ?? [], [
                    'request' => $payload,
                    'error' => $exception->getMessage(),
                ]),
                'sent_at' => now(),
            ]);

            return null;
        }

        $message->update([
            'delivery_status' => $response->successful()
                ? TelegramMessage::STATUS_SENT
                : TelegramMessage::STATUS_FAILED,
            'payload_json' => array_merge($message->payload_json ?? [], [
                'request' => $payload,
                'response' => $response->json(),
            ]),
            'sent_at' => now(),
        ]);

        Log::info('Telegram sendMessage response', [
            'message_id' => $message->id,
            'chat_id' => $message->telegram_chat_id,
            'successful' => $response->successful(),
            'status' => $response->status(),
            'response' => $response->json(),
        ]);

        return $response;
    }
}
