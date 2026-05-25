<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meal_plan_items', function (Blueprint $table) {
            $table->time('scheduled_time')->nullable()->after('meal_time');
        });

        Schema::table('meal_logs', function (Blueprint $table) {
            $table->dateTime('scheduled_for')->nullable()->after('meal_date');
        });

        Schema::create('telegram_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->nullable()->constrained()->nullOnDelete();
            $table->string('telegram_chat_id');
            $table->string('reminder_type');
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->dateTime('scheduled_for');
            $table->dateTime('reminder_due_at');
            $table->dateTime('sent_at')->nullable();
            $table->string('status')->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(
                ['reminder_type', 'related_id', 'reminder_due_at', 'telegram_chat_id'],
                'telegram_reminder_unique_idx'
            );
            $table->index(['status', 'reminder_due_at'], 'telegram_reminder_status_due_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_reminder_logs');

        Schema::table('meal_logs', function (Blueprint $table) {
            $table->dropColumn('scheduled_for');
        });

        Schema::table('meal_plan_items', function (Blueprint $table) {
            $table->dropColumn('scheduled_time');
        });
    }
};
