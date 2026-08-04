<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ambil nama tabel dari model biar nggak hardcode (aman walau namanya alokasis/lomba_kontingen)
        $table = (new \App\Models\LombaKontingen)->getTable();

        if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'kategori')) {
            Schema::table($table, function (Blueprint $t) {
                $t->enum('kategori', ['PA', 'PI'])->nullable()->after('golongan');
            });
        }
    }

    public function down(): void
    {
        $table = (new \App\Models\LombaKontingen)->getTable();
        if (Schema::hasTable($table) && Schema::hasColumn($table, 'kategori')) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn('kategori'));
        }
    }
};