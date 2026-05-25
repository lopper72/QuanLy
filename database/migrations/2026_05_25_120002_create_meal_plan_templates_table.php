<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_plan_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('goal');
            $table->text('description');
            $table->unsignedTinyInteger('week_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['goal', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_plan_templates');
    }
};
