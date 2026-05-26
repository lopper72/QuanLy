<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('behavior_logs', function (Blueprint $table) {
            $table->foreignId('training_session_id')
                ->nullable()
                ->after('child_id')
                ->constrained('training_sessions')
                ->nullOnDelete();
            $table->foreignId('training_session_item_id')
                ->nullable()
                ->after('training_session_id')
                ->constrained('training_session_items')
                ->nullOnDelete();

            $table->index(['training_session_id', 'training_session_item_id'], 'behavior_logs_training_link_index');
        });
    }

    public function down(): void
    {
        Schema::table('behavior_logs', function (Blueprint $table) {
            $table->dropIndex('behavior_logs_training_link_index');
            $table->dropConstrainedForeignId('training_session_item_id');
            $table->dropConstrainedForeignId('training_session_id');
        });
    }
};
