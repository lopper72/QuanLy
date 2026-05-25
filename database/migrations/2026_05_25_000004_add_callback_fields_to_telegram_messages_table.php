<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('telegram_messages', function (Blueprint $table) {
            $table->string('callback_data')->nullable()->after('message_text')->index();
            $table->string('action_status')->nullable()->after('callback_data')->index();
        });
    }

    public function down(): void
    {
        Schema::table('telegram_messages', function (Blueprint $table) {
            $table->dropIndex(['callback_data']);
            $table->dropIndex(['action_status']);
            $table->dropColumn(['callback_data', 'action_status']);
        });
    }
};
