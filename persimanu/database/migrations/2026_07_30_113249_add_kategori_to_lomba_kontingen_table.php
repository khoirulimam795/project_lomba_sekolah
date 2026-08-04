<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    if (Schema::hasTable('lomba_kontingen') && !Schema::hasColumn('lomba_kontingen', 'kategori')) {
        Schema::table('lomba_kontingen', function (Blueprint $t) {
            $t->enum('kategori', ['PA', 'PI'])->nullable()->after('golongan');
        });
    }
}

public function down(): void
{
    if (Schema::hasTable('lomba_kontingen') && Schema::hasColumn('lomba_kontingen', 'kategori')) {
        Schema::table('lomba_kontingen', fn (Blueprint $t) => $t->dropColumn('kategori'));
    }
}
};
