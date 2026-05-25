<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('behavior_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->cascadeOnDelete();
            $table->string('behavior_type');
            $table->string('severity')->nullable();
            $table->string('trigger')->nullable();
            $table->string('response')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['child_id', 'recorded_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('behavior_logs');
    }
};
