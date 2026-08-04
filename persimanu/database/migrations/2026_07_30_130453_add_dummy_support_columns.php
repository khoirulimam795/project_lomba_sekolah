<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // lombas: golongan + kategori (buat badge di card lomba & filter)
        if (Schema::hasTable('lombas')) {
            Schema::table('lombas', function (Blueprint $t) {
                if (!Schema::hasColumn('lombas', 'golongan')) $t->string('golongan')->nullable();
                if (!Schema::hasColumn('lombas', 'kategori')) $t->string('kategori')->nullable();
            });
        }
        // lomba_kontingen (alokasi): kategori
        if (Schema::hasTable('lomba_kontingen') && !Schema::hasColumn('lomba_kontingen', 'kategori')) {
            Schema::table('lomba_kontingen', fn (Blueprint $t) => $t->string('kategori')->nullable());
        }
        // kontingen: catatan pembayaran
        if (Schema::hasTable('kontingens') && !Schema::hasColumn('kontingens', 'catatan_pembayaran')) {
            Schema::table('kontingens', fn (Blueprint $t) => $t->text('catatan_pembayaran')->nullable());
        }
        // siswa & pendamping: slot_index
        if (Schema::hasTable('siswas') && !Schema::hasColumn('siswas', 'slot_index')) {
            Schema::table('siswas', fn (Blueprint $t) => $t->unsignedInteger('slot_index')->nullable());
        }
        if (Schema::hasTable('pendampings') && !Schema::hasColumn('pendampings', 'slot_index')) {
            Schema::table('pendampings', fn (Blueprint $t) => $t->unsignedInteger('slot_index')->nullable());
        }
    }

    public function down(): void
    {
        // no-op (kolom dummy dibiarkan)
    }
};