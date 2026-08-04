<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // defensif: cuma nambah kalau tabel ada & kolom belum ada (idempotent)
        if (Schema::hasTable('pendampings') && ! Schema::hasColumn('pendampings', 'slot_index')) {
            Schema::table('pendampings', function (Blueprint $table) {
                $table->unsignedInteger('slot_index')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pendampings') && Schema::hasColumn('pendampings', 'slot_index')) {
            Schema::table('pendampings', fn (Blueprint $t) => $t->dropColumn('slot_index'));
        }
    }
};