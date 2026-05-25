<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('training_session_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_session_id')->constrained('training_sessions')->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->string('completion_status')->nullable();
            $table->text('therapist_note')->nullable();
            $table->timestamps();

            $table->index(['training_session_id', 'exercise_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('training_session_items');
    }
};
