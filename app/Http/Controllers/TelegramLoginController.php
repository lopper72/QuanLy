<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TelegramLoginController extends Controller
{
    public function login(Request $request, User $user)
    {
        // Log the access
        Log::info('Telegram deep-link access', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Auto-authenticate the user
        Auth::login($user);

        // Redirect to /today
        return redirect()->route('today');
    }
}