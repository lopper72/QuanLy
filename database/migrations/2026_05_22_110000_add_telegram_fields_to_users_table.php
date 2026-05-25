<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_chat_id')->nullable()->index()->after('remember_token');
            $table->string('telegram_link_token')->nullable()->unique()->after('telegram_chat_id');
            $table->boolean('telegram_notifications_enabled')->default(false)->after('telegram_link_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['telegram_link_token']);
            $table->dropIndex(['telegram_chat_id']);
            $table->dropColumn([
                'telegram_chat_id',
                'telegram_link_token',
                'telegram_notifications_enabled',
            ]);
        });
    }
};
