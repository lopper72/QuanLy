<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('assessment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->string('skill_name');
            $table->unsignedSmallInteger('score')->nullable();
            $table->string('level')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('assessment_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('assessment_items');
    }
};
