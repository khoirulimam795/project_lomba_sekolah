<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Kolom opsional di form siswa yang di DB masih NOT NULL → bikin nullable.
     * Teknik: baca tipe asli dari INFORMATION_SCHEMA, lalu MODIFY ... NULL
     * sehingga tipe/length/enum/collation TETAP persis (tanpa doctrine/dbal).
     */
    public function up(): void
    {
        $columns = [
            'nisn', 'tempat_lahir', 'tanggal_lahir', 'nama_orang_tua',
            'alamat', 'no_telp', 'jenjang_pendidikan', 'golongan_pramuka', 'golongan_darah',
        ];

        foreach ($columns as $col) {
            $row = DB::selectOne(
                "SELECT COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT,
                        CHARACTER_SET_NAME, COLLATION_NAME
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'siswas'
                   AND COLUMN_NAME = ?",
                [$col]
            );

            if (! $row) continue;                    // kolom nggak ada → skip
            if ($row->IS_NULLABLE === 'YES') continue; // udah nullable → skip

            $type      = $row->COLUMN_TYPE; // mis. varchar(255), date, text, enum(...)
            $charset   = $row->CHARACTER_SET_NAME ? " CHARACTER SET {$row->CHARACTER_SET_NAME}" : '';
            $collation = $row->COLLATION_NAME     ? " COLLATE {$row->COLLATION_NAME}"         : '';
            $default   = is_null($row->COLUMN_DEFAULT) ? ' DEFAULT NULL' : '';

            DB::statement("ALTER TABLE `siswas` MODIFY `{$col}` {$type}{$charset}{$collation} NULL{$default}");
        }
    }

    public function down(): void
    {
        $columns = [
            'nisn', 'tempat_lahir', 'tanggal_lahir', 'nama_orang_tua',
            'alamat', 'no_telp', 'jenjang_pendidikan', 'golongan_pramuka', 'golongan_darah',
        ];
        foreach ($columns as $col) {
            try {
                $row = DB::selectOne(
                    "SELECT COLUMN_TYPE, CHARACTER_SET_NAME, COLLATION_NAME
                     FROM INFORMATION_SCHEMA.COLUMNS
                     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'siswas' AND COLUMN_NAME = ?",
                    [$col]
                );
                if (! $row) continue;
                $charset   = $row->CHARACTER_SET_NAME ? " CHARACTER SET {$row->CHARACTER_SET_NAME}" : '';
                $collation = $row->COLLATION_NAME     ? " COLLATE {$row->COLLATION_NAME}"         : '';
                DB::statement("ALTER TABLE `siswas` MODIFY `{$col}` {$row->COLUMN_TYPE}{$charset}{$collation} NOT NULL");
            } catch (\Throwable $e) {
                // irreversibel kalau kolom sudah berisi NULL — abaikan per kolom
            }
        }
    }
};