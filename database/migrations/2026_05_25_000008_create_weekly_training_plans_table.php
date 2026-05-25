<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_training_plans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('target_condition')->nullable();
            $table->string('recommended_age')->nullable();
            $table->timestamps();

            $table->index('target_condition');
        });

        Schema::create('weekly_training_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_plan_id')->constrained('weekly_training_plans')->cascadeOnDelete();
            $table->foreignId('exercise_id')->nullable()->constrained('exercises')->nullOnDelete();
            $table->foreignId('combo_id')->nullable()->constrained('exercise_combos')->nullOnDelete();
            $table->string('day_of_week');
            $table->string('session_time');
            $table->unsignedSmallInteger('estimated_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['weekly_plan_id', 'day_of_week', 'session_time'], 'weekly_plan_schedule_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_training_plan_items');
        Schema::dropIfExists('weekly_training_plans');
    }
};
