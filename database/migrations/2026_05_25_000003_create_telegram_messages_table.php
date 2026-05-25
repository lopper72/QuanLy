<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_messages', function (Blueprint $table) {
            $table->id();
            $table->string('direction')->index();
            $table->string('telegram_chat_id')->index();
            $table->string('telegram_user_id')->nullable()->index();
            $table->string('telegram_username')->nullable();
            $table->string('message_type')->default('text');
            $table->text('message_text')->nullable();
            $table->json('payload_json')->nullable();
            $table->string('delivery_status')->default('pending')->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->foreignId('related_child_id')->nullable()->constrained('children')->nullOnDelete();
            $table->foreignId('related_training_id')->nullable()->constrained('training_sessions')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_messages');
    }
};
