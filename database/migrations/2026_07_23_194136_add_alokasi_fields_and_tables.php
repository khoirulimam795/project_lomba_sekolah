<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) lomba_kontingen: tambah pendamping (1 pendamping per alokasi)
        Schema::table('lomba_kontingen', function (Blueprint $table) {
            if (!Schema::hasColumn('lomba_kontingen', 'pendamping_id')) {
                $table->foreignId('pendamping_id')
                    ->nullable()
                    ->after('golongan')
                    ->constrained('pendampings')
                    ->nullOnDelete();
            }
        });

        // 2) pivot siswa per alokasi (max 10 di-enforce di backend)
        if (!Schema::hasTable('lomba_kontingen_siswa')) {
            Schema::create('lomba_kontingen_siswa', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lomba_kontingen_id')
                    ->constrained('lomba_kontingen')
                    ->cascadeOnDelete();
                $table->foreignId('siswa_id')
                    ->constrained('siswas')
                    ->cascadeOnDelete();
                $table->timestamps();

                // 1 siswa cuma 1x dalam 1 alokasi
                $table->unique(['lomba_kontingen_id', 'siswa_id'], 'lks_alokasi_siswa_unique');
                $table->index('siswa_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lomba_kontingen_siswa');
        Schema::table('lomba_kontingen', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pendamping_id');
        });
    }
};