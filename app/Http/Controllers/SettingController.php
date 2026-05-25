<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(Request $request, TelegramService $telegramService): Response
    {
        $user = $request->user();

        return Inertia::render('Settings/Index', [
            'settings' => [
                'system_name' => 'Hệ thống quản lý can thiệp cho trẻ',
                'version' => '1.0.0',
                'timezone' => 'Asia/Ho_Chi_Minh',
                'notifications_enabled' => true,
            ],
            'telegram' => [
                'connected' => filled($user?->telegram_chat_id),
                'notifications_enabled' => (bool) $user?->telegram_notifications_enabled,
                'link_url' => filled($user?->telegram_link_token) ? $telegramService->linkUrl($user) : null,
            ],
        ]);
    }

    public function telegramLink(Request $request, TelegramService $telegramService): RedirectResponse
    {
        $telegramService->ensureLinkToken($request->user());

        return back()->with('success', 'Đã tạo liên kết Telegram.');
    }
}
