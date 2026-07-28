<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            if (!Schema::hasColumn('teams', 'user_id')) {
                $table->foreignId('user_id')->nullable()->index();
            }

            if (!Schema::hasColumn('teams', 'name')) {
                $table->string('name')->nullable();
            }

            if (!Schema::hasColumn('teams', 'personal_team')) {
                $table->boolean('personal_team')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'name', 'personal_team']);
        });
    }
};