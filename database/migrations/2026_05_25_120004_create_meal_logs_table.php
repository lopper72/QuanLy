<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meal_plan_item_id')->nullable()->constrained()->nullOnDelete();
            $table->date('meal_date');
            $table->string('status')->default('planned');
            $table->text('notes')->nullable();
            $table->text('stool_note')->nullable();
            $table->text('water_note')->nullable();
            $table->timestamps();

            $table->index(['child_id', 'meal_date']);
            $table->index(['meal_plan_item_id', 'meal_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_logs');
    }
};
