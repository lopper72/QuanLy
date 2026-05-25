<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->cascadeOnDelete();
            $table->date('checklist_date');
            $table->string('context_mode')->default('home');
            $table->text('summary')->nullable();
            $table->timestamps();

            $table->unique(['child_id', 'checklist_date']);
            $table->index(['checklist_date', 'context_mode']);
        });

        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_checklist_id')->constrained('daily_checklists')->cascadeOnDelete();
            $table->foreignId('training_session_item_id')->nullable()->constrained('training_session_items')->nullOnDelete();
            $table->foreignId('carried_over_from_id')->nullable()->constrained('checklist_items')->nullOnDelete();
            $table->string('status')->default('not_started');
            $table->string('performance_result')->nullable();
            $table->text('parent_note')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('carried_over_at')->nullable();
            $table->timestamps();

            $table->unique('training_session_item_id');
            $table->index(['daily_checklist_id', 'status']);
        });

        Schema::create('checklist_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_checklist_id')->constrained('daily_checklists')->cascadeOnDelete();
            $table->unsignedInteger('total_items')->default(0);
            $table->unsignedInteger('completed_items')->default(0);
            $table->unsignedInteger('remaining_items')->default(0);
            $table->unsignedTinyInteger('completion_percent')->default(0);
            $table->timestamps();

            $table->unique('daily_checklist_id');
        });

        Schema::create('parent_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->cascadeOnDelete();
            $table->foreignId('checklist_item_id')->nullable()->constrained('checklist_items')->nullOnDelete();
            $table->text('note');
            $table->timestamp('noted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('daily_moods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->cascadeOnDelete();
            $table->date('mood_date');
            $table->string('mood');
            $table->timestamps();

            $table->unique(['child_id', 'mood_date']);
        });

        Schema::create('progress_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->cascadeOnDelete();
            $table->string('title');
            $table->text('note')->nullable();
            $table->timestamp('logged_at')->nullable();
            $table->timestamps();
        });

        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->cascadeOnDelete();
            $table->foreignId('checklist_item_id')->nullable()->constrained('checklist_items')->nullOnDelete();
            $table->timestamp('remind_at');
            $table->string('channel')->default('in_app');
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index(['child_id', 'remind_at', 'status']);
        });

        Schema::create('streak_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->cascadeOnDelete();
            $table->unsignedInteger('current_streak')->default(0);
            $table->unsignedInteger('best_streak')->default(0);
            $table->date('last_completed_date')->nullable();
            $table->timestamps();

            $table->unique('child_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streak_trackings');
        Schema::dropIfExists('reminders');
        Schema::dropIfExists('progress_logs');
        Schema::dropIfExists('daily_moods');
        Schema::dropIfExists('parent_notes');
        Schema::dropIfExists('checklist_progress');
        Schema::dropIfExists('checklist_items');
        Schema::dropIfExists('daily_checklists');
    }
};
