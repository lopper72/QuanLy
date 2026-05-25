<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_plan_template_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->string('meal_time');
            $table->string('title');
            $table->json('foods_json');
            $table->text('constipation_support_note')->nullable();
            $table->text('parent_tip')->nullable();
            $table->timestamps();

            $table->index(['meal_plan_template_id', 'day_of_week', 'meal_time'], 'meal_items_template_day_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_plan_items');
    }
};
