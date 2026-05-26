<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_meal_suggestion_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->string('telegram_chat_id');
            $table->date('suggestion_date');
            $table->foreignId('meal_plan_item_id')->nullable()->constrained()->nullOnDelete();
            $table->text('message_text');
            $table->timestamp('sent_at')->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['child_id', 'suggestion_date', 'telegram_chat_id'], 'telegram_meal_suggestion_unique_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_meal_suggestion_logs');
    }
};
