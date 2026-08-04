<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('lombas', 'golongan')) {
            Schema::table('lombas', fn (Blueprint $t) =>
                $t->enum('golongan', ['penggalang_ramu', 'penggalang', 'penegak'])->nullable()->after('deskripsi'));
        }
        if (! Schema::hasColumn('lombas', 'kategori')) {
            Schema::table('lombas', fn (Blueprint $t) =>
                $t->enum('kategori', ['PA', 'PI'])->nullable()->after('golongan'));
        }
    }

    public function down(): void
    {
        Schema::table('lombas', function (Blueprint $t) {
            if (Schema::hasColumn('lombas', 'kategori')) $t->dropColumn('kategori');
            if (Schema::hasColumn('lombas', 'golongan')) $t->dropColumn('golongan');
        });
    }
};