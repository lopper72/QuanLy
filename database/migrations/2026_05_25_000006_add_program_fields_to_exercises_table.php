<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->text('description')->nullable()->after('instructions');
            $table->string('target_skill')->nullable()->after('description');
            $table->string('recommended_age')->nullable()->after('target_skill');
            $table->text('required_tools')->nullable()->after('recommended_age');
            $table->text('expected_benefits')->nullable()->after('required_tools');
            $table->text('safety_notes')->nullable()->after('expected_benefits');
            $table->text('parent_tips')->nullable()->after('safety_notes');
            $table->text('weekly_expectation')->nullable()->after('parent_tips');

            $table->index('target_skill');
        });
    }

    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->dropIndex(['target_skill']);
            $table->dropColumn([
                'description',
                'target_skill',
                'recommended_age',
                'required_tools',
                'expected_benefits',
                'safety_notes',
                'parent_tips',
                'weekly_expectation',
            ]);
        });
    }
};
