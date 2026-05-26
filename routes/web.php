<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\BehaviorController;
use App\Http\Controllers\ChildController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DailyChecklistController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\MealPlanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupplementController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\WeeklyReportController;

Route::get('/', function () {
    return redirect()->route('today');
});

Route::post('/webhooks/telegram', TelegramWebhookController::class)->name('webhooks.telegram');
Route::post('/telegram/webhook', TelegramWebhookController::class)->name('telegram.webhook');
Route::get('/telegram/login/{user}', [App\Http\Controllers\TelegramLoginController::class, 'login'])
    ->name('telegram.login')
    ->middleware('signed');

Route::middleware(['auth'])->group(function () {
    // Profile Management (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dashboard Route
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Parent Daily Checklist
    Route::get('/today', [DailyChecklistController::class, 'today'])->name('today');
    Route::get('/checklist/today', [DailyChecklistController::class, 'today'])->name('checklist.today');
    Route::patch('/checklist/items/{checklistItem}', [DailyChecklistController::class, 'updateItem'])->name('checklist.items.update');
    Route::patch('/checklist/items/{checklistItem}/quick-complete', [DailyChecklistController::class, 'quickComplete'])->name('checklist.items.quickComplete');
    Route::post('/checklist/items/{checklistItem}/carry-over', [DailyChecklistController::class, 'carryOver'])->name('checklist.items.carryOver');
    Route::post('/checklist/children/{child}/mood', [DailyChecklistController::class, 'mood'])->name('checklist.children.mood');
    Route::post('/checklist/children/{child}/progress-log', [DailyChecklistController::class, 'progressLog'])->name('checklist.children.progressLog');

    // Children Management Routes
    Route::prefix('children')->name('children.')->group(function () {
        Route::get('/', [ChildController::class, 'index'])->name('index');
        Route::get('/create', [ChildController::class, 'create'])->name('create');
        Route::post('/', [ChildController::class, 'store'])->name('store');
        Route::patch('/{child}/pause', [ChildController::class, 'pause'])->name('pause');
        Route::patch('/{child}/activate', [ChildController::class, 'activate'])->name('activate');
        Route::post('/{child}/resume', [ChildController::class, 'resume'])->name('resume');
        Route::patch('/{child}/void', [ChildController::class, 'void'])->name('void');
        Route::get('/{child}', [ChildController::class, 'show'])->name('show');
        Route::get('/{child}/edit', [ChildController::class, 'edit'])->name('edit');
        Route::put('/{child}', [ChildController::class, 'update'])->name('update');
        Route::delete('/{child}', [ChildController::class, 'destroy'])->name('destroy');
    });

    // Daily Training Routes
    Route::prefix('training')->name('training.')->group(function () {
        Route::get('/today', [TrainingController::class, 'today'])->name('today');
        Route::post('/today/telegram-send', [TrainingController::class, 'sendTodayTelegram'])->name('today.telegramSend');
        Route::get('/', [TrainingController::class, 'index'])->name('index');
        Route::get('/create', [TrainingController::class, 'create'])->name('create');
        Route::post('/', [TrainingController::class, 'store'])->name('store');
        Route::get('/{trainingSession}', [TrainingController::class, 'show'])->name('show');
        Route::post('/{trainingSession}/telegram-send', [TrainingController::class, 'sendTelegram'])->name('telegramSend');
        Route::get('/{trainingSession}/edit', [TrainingController::class, 'edit'])->name('edit');
        Route::put('/{trainingSession}', [TrainingController::class, 'update'])->name('update');
        Route::delete('/{trainingSession}', [TrainingController::class, 'destroy'])->name('destroy');
        Route::patch('/{trainingSession}/status', [TrainingController::class, 'updateStatus'])->name('updateStatus');
        Route::patch('/{trainingSession}/quick-note', [TrainingController::class, 'quickNote'])->name('quickNote');
        Route::patch('/items/{trainingSessionItem}/status', [TrainingController::class, 'updateItemStatus'])->name('updateItemStatus');
        Route::delete('/unknown-groups/{groupKey}', [TrainingController::class, 'destroyUnknownGroup'])->name('destroyUnknownGroup');
    });

    Route::patch('/training-items/{trainingSessionItem}/quick-complete', [TrainingController::class, 'quickComplete'])->name('trainingSessionItem.quickComplete');
    Route::patch('/training-items/{trainingSessionItem}/quick-skip', [TrainingController::class, 'quickSkip'])->name('trainingSessionItem.quickSkip');

    Route::prefix('supplements')->name('supplements.')->group(function () {
        Route::get('/', [SupplementController::class, 'index'])->name('index');
        Route::get('/create', [SupplementController::class, 'create'])->name('create');
        Route::post('/', [SupplementController::class, 'store'])->name('store');
        Route::get('/{supplement}/edit', [SupplementController::class, 'edit'])->name('edit');
        Route::put('/{supplement}', [SupplementController::class, 'update'])->name('update');
        Route::patch('/{supplement}/taken', [SupplementController::class, 'markTaken'])->name('taken');
        Route::patch('/{supplement}/skip', [SupplementController::class, 'skip'])->name('skip');
    });

    Route::prefix('meal-plans')->name('mealPlans.')->group(function () {
        Route::get('/', [MealPlanController::class, 'index'])->name('index');
        Route::post('/apply', [MealPlanController::class, 'apply'])->name('apply');
        Route::post('/logs', [MealPlanController::class, 'log'])->name('logs.store');
        Route::post('/telegram/dinner-suggestion', [MealPlanController::class, 'sendDinnerSuggestion'])->name('telegram.dinnerSuggestion');
        Route::post('/telegram/alternative-dinner', [MealPlanController::class, 'sendAlternativeDinner'])->name('telegram.alternativeDinner');
        Route::post('/telegram/today-schedule', [MealPlanController::class, 'sendTodayMealSchedule'])->name('telegram.todaySchedule');
    });

    // Exercise Library Routes
    Route::prefix('exercises')->name('exercises.')->group(function () {
        Route::get('/', [ExerciseController::class, 'index'])->name('index');
        Route::get('/create', [ExerciseController::class, 'create'])->name('create');
        Route::post('/', [ExerciseController::class, 'store'])->name('store');
        Route::get('/{exercise}', [ExerciseController::class, 'show'])->name('show');
        Route::get('/{exercise}/edit', [ExerciseController::class, 'edit'])->name('edit');
        Route::put('/{exercise}', [ExerciseController::class, 'update'])->name('update');
        Route::delete('/{exercise}', [ExerciseController::class, 'destroy'])->name('destroy');
    });

    // Assessment Routes
    Route::prefix('assessment')->name('assessment.')->group(function () {
        Route::get('/', [AssessmentController::class, 'index'])->name('index');
        Route::get('/progress', [AssessmentController::class, 'progress'])->name('progress');
        Route::get('/create', [AssessmentController::class, 'create'])->name('create');
        Route::post('/', [AssessmentController::class, 'store'])->name('store');
        Route::get('/{assessment}', [AssessmentController::class, 'show'])->name('show');
        Route::get('/{assessment}/edit', [AssessmentController::class, 'edit'])->name('edit');
        Route::put('/{assessment}', [AssessmentController::class, 'update'])->name('update');
        Route::delete('/{assessment}', [AssessmentController::class, 'destroy'])->name('destroy');
    });

    // Behavior Tracking Routes
    Route::prefix('behavior')->name('behavior.')->group(function () {
        Route::get('/quick', [BehaviorController::class, 'quick'])->name('quick');
        Route::post('/quick-store', [BehaviorController::class, 'quickStore'])->name('quickStore');
        Route::get('/', [BehaviorController::class, 'index'])->name('index');
        Route::get('/create', [BehaviorController::class, 'create'])->name('create');
        Route::post('/', [BehaviorController::class, 'store'])->name('store');
        Route::get('/{behaviorLog}', [BehaviorController::class, 'show'])->name('show');
        Route::get('/{behaviorLog}/edit', [BehaviorController::class, 'edit'])->name('edit');
        Route::put('/{behaviorLog}', [BehaviorController::class, 'update'])->name('update');
        Route::delete('/{behaviorLog}', [BehaviorController::class, 'destroy'])->name('destroy');
    });

    // Reports Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        // Weekly Report Routes
        Route::get('/weekly', [WeeklyReportController::class, 'index'])->name('weekly.index');
        Route::post('/weekly/generate', [WeeklyReportController::class, 'generate'])->name('weekly.generate');
        Route::get('/weekly/{child}/download', [WeeklyReportController::class, 'download'])->name('weekly.download');

        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/create', [ReportController::class, 'create'])->name('create');
        Route::post('/', [ReportController::class, 'store'])->name('store');
        Route::get('/{report}', [ReportController::class, 'show'])->name('show');
        Route::get('/{report}/edit', [ReportController::class, 'edit'])->name('edit');
        Route::put('/{report}', [ReportController::class, 'update'])->name('update');
        Route::delete('/{report}', [ReportController::class, 'destroy'])->name('destroy');
    });

    // Settings Route
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/telegram/link', [SettingController::class, 'telegramLink'])->name('settings.telegram.link');

    Route::prefix('telegram')->name('telegram.')->group(function () {
        Route::get('/', [TelegramController::class, 'index'])->name('index');
        Route::get('/settings', [TelegramController::class, 'settings'])->name('settings');
        Route::patch('/settings', [TelegramController::class, 'updateSettings'])->name('settings.update');
        Route::post('/webhook/register', [TelegramController::class, 'registerWebhook'])->name('webhook.register');
        Route::post('/webhook/delete', [TelegramController::class, 'deleteWebhook'])->name('webhook.delete');
        Route::get('/health', [TelegramController::class, 'health'])->name('health');
        Route::post('/test-send', [TelegramController::class, 'testSend'])->name('testSend');
        Route::post('/training/send-today', [TelegramController::class, 'sendTodayTraining'])->name('training.sendToday');
        Route::post('/training/simulate-callback', [TelegramController::class, 'simulateTrainingCallback'])->name('training.simulateCallback');
        Route::post('/test/bot', [TelegramController::class, 'testBot'])->name('test.bot');
        Route::post('/test/webhook-info', [TelegramController::class, 'testWebhookInfo'])->name('test.webhookInfo');
        Route::post('/test/send-message', [TelegramController::class, 'testSendMessage'])->name('test.sendMessage');
        Route::post('/test/training-schedule', [TelegramController::class, 'testTrainingSchedule'])->name('test.trainingSchedule');
        Route::post('/test/reminder/training', [TelegramController::class, 'testReminderTraining'])->name('test.reminder.training');
        Route::post('/test/reminder/meal', [TelegramController::class, 'testReminderMeal'])->name('test.reminder.meal');
        Route::post('/test/reminder/supplement', [TelegramController::class, 'testReminderSupplement'])->name('test.reminder.supplement');
        Route::post('/test/meal-suggestion/dinner', [TelegramController::class, 'testDinnerSuggestion'])->name('test.mealSuggestion.dinner');
        Route::post('/test/meal-suggestion/an', [TelegramController::class, 'testMealCommandAn'])->name('test.mealSuggestion.an');
        Route::post('/test/meal-suggestion/doimon', [TelegramController::class, 'testMealCommandDoimon'])->name('test.mealSuggestion.doimon');
        Route::post('/test/meal-suggestion/callback', [TelegramController::class, 'testMealSuggestionCallback'])->name('test.mealSuggestion.callback');
        Route::post('/test/callback/simulate', [TelegramController::class, 'simulateCallback'])->name('test.callback.simulate');
        Route::get('/webhook-info', [TelegramController::class, 'webhookInfo'])->name('webhookInfo');
        Route::get('/messages', [TelegramController::class, 'messages'])->name('messages');
        Route::post('/send', [TelegramController::class, 'send'])->name('send');
    });
});

require __DIR__.'/auth.php';
