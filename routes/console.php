<?php

use App\Models\ChecklistItem;
use App\Models\DailyChecklist;
use App\Models\Exercise;
use App\Models\Reminder;
use App\Models\User;
use App\Services\TelegramService;
use App\Services\TrainingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

Artisan::command('exercises:generate-placeholders', function () {
    $disk = Storage::disk('public');
    $baseDirectory = 'exercises/placeholders';
    $disk->makeDirectory($baseDirectory);

    $queries = [
        'gross_motor' => 'child movement exercise jumping mat',
        'fine_motor' => 'child hand coordination activity beads',
        'sensory' => 'child sensory play activity rice tray',
        'communication' => 'child communication therapy activity',
        'cognitive' => 'child sorting colors learning activity',
        'social' => 'children turn taking play activity',
        'self_care' => 'child cleaning up toys self care activity',
    ];

    $updated = 0;
    $skipped = 0;

    Exercise::whereNull('thumbnail_path')
        ->orderBy('id')
        ->chunkById(50, function ($exercises) use ($disk, $baseDirectory, $queries, &$updated, &$skipped) {
            foreach ($exercises as $exercise) {
                $category = $exercise->category ?: 'default';
                $categoryPath = "{$baseDirectory}/{$category}.jpg";

                if (!$disk->exists($categoryPath)) {
                    $query = $queries[$category] ?? "{$exercise->title} child therapy activity";
                    $downloaded = downloadExercisePlaceholder($query);

                    if ($downloaded) {
                        $disk->put($categoryPath, $downloaded);
                    } else {
                        $categoryPath = "{$baseDirectory}/{$category}.svg";
                        if (!$disk->exists($categoryPath)) {
                            $disk->put($categoryPath, exercisePlaceholderSvg($exercise->title, $category));
                        }
                    }
                }

                if ($exercise->thumbnail_path) {
                    $skipped++;
                    continue;
                }

                $exercise->forceFill(['thumbnail_path' => $categoryPath])->save();
                $updated++;
            }
        });

    $this->info("Đã gán ảnh minh họa cho {$updated} bài tập.");
    if ($skipped > 0) {
        $this->line("Đã bỏ qua {$skipped} bài tập đã có ảnh.");
    }
})->purpose('Tải và gán ảnh minh họa nội bộ cho bài tập chưa có thumbnail');

Artisan::command('telegram:send-reminders', function (TelegramService $telegramService) {
    $users = User::query()
        ->whereNotNull('telegram_chat_id')
        ->where('telegram_notifications_enabled', true)
        ->get();

    if ($users->isEmpty()) {
        $this->info('Chưa có phụ huynh kết nối Telegram.');

        return 0;
    }

    $sent = 0;
    $now = now();
    $today = today();

    $checklists = DailyChecklist::with(['child', 'items.trainingSessionItem.exercise'])
        ->whereDate('checklist_date', $today)
        ->whereHas('child', fn ($query) => $query->active())
        ->get();

    foreach ($checklists as $checklist) {
        $totalItems = $checklist->items->count();

        if ($totalItems > 0 && $now->hour >= 6) {
            $morningReminder = Reminder::firstOrCreate(
                [
                    'child_id' => $checklist->child_id,
                    'checklist_item_id' => null,
                    'remind_at' => $today->copy()->setTime(6, 0),
                    'channel' => 'telegram_morning',
                ],
                ['status' => 'pending']
            );

            if ($morningReminder->status !== 'sent') {
                foreach ($users as $user) {
                    $telegramService->queueMorningChecklist($user, $checklist);
                    $sent++;
                }
                $morningReminder->update(['status' => 'sent']);
            }
        }

        if ($totalItems > 0 && $now->hour >= 20) {
            $completed = $checklist->items->where('status', ChecklistItem::STATUS_COMPLETED)->count();
            $unfinished = $checklist->items->whereIn('status', [
                ChecklistItem::STATUS_PENDING,
                ChecklistItem::STATUS_NOT_STARTED,
                ChecklistItem::STATUS_IN_PROGRESS,
            ])->count();
            $endReminder = Reminder::firstOrCreate(
                [
                    'child_id' => $checklist->child_id,
                    'checklist_item_id' => null,
                    'remind_at' => $today->copy()->setTime(20, 0),
                    'channel' => 'telegram_end_of_day',
                ],
                ['status' => 'pending']
            );

            if ($endReminder->status !== 'sent') {
                foreach ($users as $user) {
                    $telegramService->queueEndOfDayReport($user, $checklist->child->full_name, $completed, $totalItems, $unfinished);
                    $sent++;
                }
                $endReminder->update(['status' => 'sent']);
            }
        }
    }

    $items = ChecklistItem::with(['dailyChecklist.child', 'trainingSessionItem.trainingSession', 'trainingSessionItem.exercise'])
        ->whereIn('status', [ChecklistItem::STATUS_PENDING, ChecklistItem::STATUS_NOT_STARTED, ChecklistItem::STATUS_IN_PROGRESS])
        ->whereHas('dailyChecklist', function ($query) use ($today) {
            $query->whereDate('checklist_date', $today)
                ->whereHas('child', fn ($childQuery) => $childQuery->active());
        })
        ->whereHas('trainingSessionItem.trainingSession', function ($query) use ($today) {
            $query->whereDate('session_date', $today)
                ->whereNotNull('scheduled_time');
        })
        ->get()
        ->filter(function (ChecklistItem $item) use ($today, $now) {
            $scheduledAt = Carbon::parse($today->toDateString().' '.$item->trainingSessionItem->trainingSession->scheduled_time);
            $remindAt = $scheduledAt->copy()->subMinutes(15);

            return $remindAt->lessThanOrEqualTo($now) && $scheduledAt->greaterThanOrEqualTo($now->copy()->subMinute());
        });

    foreach ($items as $item) {
        $scheduledAt = Carbon::parse($today->toDateString().' '.$item->trainingSessionItem->trainingSession->scheduled_time);
        $reminder = Reminder::firstOrCreate(
            [
                'child_id' => $item->dailyChecklist->child_id,
                'checklist_item_id' => $item->id,
                'remind_at' => $scheduledAt->copy()->subMinutes(15),
                'channel' => 'telegram',
            ],
            ['status' => 'pending']
        );

        if ($reminder->status === 'sent') {
            continue;
        }

        foreach ($users as $user) {
            $telegramService->queueChecklistReminder($item, $user);
            $sent++;
        }

        $reminder->update(['status' => 'sent']);
    }

    $this->info("Đã đưa {$sent} tin nhắn Telegram vào hàng đợi.");

    return 0;
})->purpose('Gửi nhắc lịch Telegram cho checklist hằng ngày');

Artisan::command('telegram:webhook:set', function (TelegramService $telegramService) {
    $response = $telegramService->setWebhook();

    if (!$response) {
        $this->error('Chưa cấu hình bot token hoặc webhook URL.');

        return 1;
    }

    $this->line(json_encode($response->json(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    return $response->successful() ? 0 : 1;
})->purpose('Đăng ký webhook Telegram production');

Artisan::command('telegram:webhook:info', function (TelegramService $telegramService) {
    $response = $telegramService->getWebhookInfo();

    if (!$response) {
        $this->error('Chưa cấu hình bot token.');

        return 1;
    }

    $this->line(json_encode($response->json(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    return $response->successful() ? 0 : 1;
})->purpose('Xem trạng thái webhook Telegram');

Artisan::command('telegram:webhook:delete', function (TelegramService $telegramService) {
    $response = $telegramService->deleteWebhook();

    if (!$response) {
        $this->error('Chưa cấu hình bot token.');

        return 1;
    }

    $this->line(json_encode($response->json(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    return $response->successful() ? 0 : 1;
})->purpose('Xóa webhook Telegram');

Schedule::command('telegram:send-reminders')->everyMinute();

Artisan::command('training:close-missed', function (TrainingService $trainingService) {
    $count = $trainingService->closeMissedSessions();

    $this->info("Đã đóng {$count} buổi tập quá ngày chưa hoàn thành.");

    return 0;
})->purpose('Đóng các buổi tập quá ngày chưa hoàn thành');

Schedule::command('training:close-missed')->dailyAt('00:05');

if (!function_exists('downloadExercisePlaceholder')) {
    function downloadExercisePlaceholder(string $query): ?string
    {
        $url = 'https://source.unsplash.com/800x600/?' . rawurlencode($query . ' child friendly');

        try {
            $response = Http::timeout(15)
                ->retry(2, 300)
                ->withOptions(['allow_redirects' => true])
                ->get($url);
        } catch (Throwable) {
            return null;
        }

        $contentType = strtolower($response->header('Content-Type', ''));

        if (!$response->successful() || !Str::startsWith($contentType, 'image/')) {
            return null;
        }

        return $response->body();
    }
}

if (!function_exists('exercisePlaceholderSvg')) {
    function exercisePlaceholderSvg(string $title, string $category): string
    {
        $safeTitle = e(Str::limit($title ?: 'Bài tập can thiệp', 42));
        $safeCategory = e($category);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600" role="img" aria-label="{$safeTitle}">
  <rect width="800" height="600" fill="#eef2ff"/>
  <circle cx="540" cy="170" r="88" fill="#bfdbfe"/>
  <circle cx="260" cy="220" r="62" fill="#a7f3d0"/>
  <rect x="145" y="374" width="510" height="52" rx="26" fill="#c7d2fe"/>
  <path d="M280 310c60-46 124-54 190-24 48 22 88 58 120 108" fill="none" stroke="#4f46e5" stroke-width="32" stroke-linecap="round"/>
  <path d="M292 308l-42 88m190-105l-34 112m80-76l72 70" fill="none" stroke="#0f766e" stroke-width="26" stroke-linecap="round"/>
  <circle cx="294" cy="266" r="34" fill="#f59e0b"/>
  <text x="400" y="500" text-anchor="middle" fill="#334155" font-family="Arial, sans-serif" font-size="34" font-weight="700">{$safeTitle}</text>
  <text x="400" y="542" text-anchor="middle" fill="#64748b" font-family="Arial, sans-serif" font-size="20">{$safeCategory}</text>
</svg>
SVG;
    }
}
