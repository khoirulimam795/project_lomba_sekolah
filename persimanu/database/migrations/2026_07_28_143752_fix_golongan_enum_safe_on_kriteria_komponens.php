<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // STEP 1: Perluas ENUM dulu (tambah nilai baru TANPA menghapus nilai lama)
        // Ini aman karena tidak ada data yang hilang
        DB::statement("ALTER TABLE `kriteria_komponens`
            MODIFY `golongan` ENUM('siaga','penggalang','penegak','pandega','penggalang_ramu') NULL");

        // STEP 2: Sekarang UPDATE aman karena 'penggalang_ramu' sudah ada di ENUM
        DB::statement("UPDATE `kriteria_komponens` SET `golongan` = 'penggalang_ramu' WHERE `golongan` = 'siaga'");
        DB::statement("UPDATE `kriteria_komponens` SET `golongan` = 'penegak' WHERE `golongan` = 'pandega'");

        // STEP 3: Baru persempit ENUM ke 3 nilai final (data lama sudah di-mapping)
        DB::statement("ALTER TABLE `kriteria_komponens`
            MODIFY `golongan` ENUM('penggalang_ramu','penggalang','penegak') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `kriteria_komponens`
            MODIFY `golongan` ENUM('siaga','penggalang','penegak','pandega') NULL");
    }
};