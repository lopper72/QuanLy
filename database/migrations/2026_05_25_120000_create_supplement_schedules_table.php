<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplement_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->nullable();
            $table->text('dosage_note')->nullable();
            $table->string('timing_type');
            $table->time('scheduled_time')->nullable();
            $table->string('meal_relation')->nullable();
            $table->string('frequency')->default('daily');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['child_id', 'status']);
            $table->index(['timing_type', 'scheduled_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplement_schedules');
    }
};
