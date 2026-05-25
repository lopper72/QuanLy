<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('children', function (Blueprint $table) {
            if (!Schema::hasColumn('children', 'status')) {
                $table->string('status')->default('active')->index()->after('notes');
            }

            if (!Schema::hasColumn('children', 'paused_at')) {
                $table->dateTime('paused_at')->nullable()->after('status');
            }

            if (!Schema::hasColumn('children', 'voided_at')) {
                $table->dateTime('voided_at')->nullable()->after('paused_at');
            }

            if (!Schema::hasColumn('children', 'status_note')) {
                $table->text('status_note')->nullable()->after('voided_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            if (Schema::hasColumn('children', 'status_note')) {
                $table->dropColumn('status_note');
            }

            if (Schema::hasColumn('children', 'voided_at')) {
                $table->dropColumn('voided_at');
            }

            if (Schema::hasColumn('children', 'paused_at')) {
                $table->dropColumn('paused_at');
            }

            if (Schema::hasColumn('children', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
