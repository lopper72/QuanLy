<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercise_combos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('target_skill')->nullable();
            $table->unsignedSmallInteger('estimated_minutes')->nullable();
            $table->string('difficulty')->nullable();
            $table->string('recommended_frequency')->nullable();
            $table->text('parent_instructions')->nullable();
            $table->timestamps();

            $table->index('target_skill');
        });

        Schema::create('exercise_combo_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combo_id')->constrained('exercise_combos')->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['combo_id', 'exercise_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_combo_items');
        Schema::dropIfExists('exercise_combos');
    }
};
