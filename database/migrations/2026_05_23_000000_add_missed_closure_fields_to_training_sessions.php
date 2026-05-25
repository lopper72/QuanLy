<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->timestamp('closed_at')->nullable()->after('notes');
            $table->string('auto_closed_reason')->nullable()->after('closed_at');
            $table->index(['session_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->dropIndex(['session_date', 'status']);
            $table->dropColumn(['closed_at', 'auto_closed_reason']);
        });
    }
};
