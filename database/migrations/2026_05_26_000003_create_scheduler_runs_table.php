<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduler_runs', function (Blueprint $table) {
            $table->id();
            $table->string('command');
            $table->dateTime('ran_at');
            $table->string('status')->default('success');
            $table->text('output_summary')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['command', 'ran_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduler_runs');
    }
};
