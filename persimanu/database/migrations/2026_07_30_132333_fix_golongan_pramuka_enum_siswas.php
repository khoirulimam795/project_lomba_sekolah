<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('siswas') || !Schema::hasColumn('siswas', 'golongan_pramuka')) {
            return;
        }

        // Cek tipe kolom saat ini
        $col = DB::selectOne(
            "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'siswas' AND COLUMN_NAME = 'golongan_pramuka'"
        );

        if (!$col) return;

        // Kalau sudah benar (3 nilai baru), skip
        if (str_contains($col->COLUMN_TYPE, 'penggalang_ramu')) {
            return;
        }

        // TAHAP 1: Perluas enum (tambah nilai baru tanpa hapus lama)
        DB::statement("ALTER TABLE `siswas` MODIFY `golongan_pramuka` ENUM('siaga','penggalang','penegak','pandega','penggalang_ramu') NULL");

        // TAHAP 2: Mapping data lama → baru (kalau ada data existing)
        DB::update("UPDATE `siswas` SET `golongan_pramuka` = 'penggalang_ramu' WHERE `golongan_pramuka` = 'siaga'");
        DB::update("UPDATE `siswas` SET `golongan_pramuka` = 'penegak' WHERE `golongan_pramuka` = 'pandega'");

        // TAHAP 3: Persempit ke 3 nilai final
        DB::statement("ALTER TABLE `siswas` MODIFY `golongan_pramuka` ENUM('penggalang_ramu','penggalang','penegak') NULL");
    }

    public function down(): void
    {
        // No-op: rollback tidak aman karena data sudah di-mapping
    }
};