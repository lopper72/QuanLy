<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->cascadeOnDelete();
            $table->string('report_type');
            $table->date('report_date');
            $table->text('summary')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['child_id', 'report_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
};
