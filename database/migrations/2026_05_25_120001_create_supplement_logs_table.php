<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplement_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplement_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->date('scheduled_for');
            $table->timestamp('taken_at')->nullable();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['supplement_schedule_id', 'scheduled_for']);
            $table->index(['child_id', 'scheduled_for']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplement_logs');
    }
};
