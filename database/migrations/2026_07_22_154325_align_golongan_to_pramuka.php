<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // AMAN: ketiga table ini masih kosong (modul kontingen/penilaian/juara belum dibangun).
        // Drop + create ulang biar enum golongan seragam = golongan Pramuka.

        Schema::dropIfExists('penilaian_details'); // kalau sudah ada & kosong, biar FK nggak nggantung
        Schema::dropIfExists('penilaians');
        Schema::dropIfExists('lomba_kontingen');
        Schema::dropIfExists('juaras');

        // --- juaras ---
        Schema::create('juaras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('lomba_id')->constrained('lombas')->cascadeOnDelete();
            $table->foreignId('kontingen_id')->constrained('kontingens')->cascadeOnDelete();
            $table->enum('golongan', ['siaga', 'penggalang', 'penegak', 'pandega']);
            $table->unsignedTinyInteger('juara');
            $table->enum('medali', ['emas', 'perak', 'perunggu']);
            $table->decimal('nilai_akhir', 5, 2)->default(0);
            $table->boolean('is_final')->default(false);
            $table->timestamps();
            $table->unique(['lomba_id', 'kontingen_id', 'golongan'], 'juaras_lomba_kontingen_golongan_unique');
            $table->index(['event_id', 'golongan']);
            $table->index(['event_id', 'medali']);
        });

        // --- lomba_kontingen ---
        Schema::create('lomba_kontingen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lomba_id')->constrained('lombas')->cascadeOnDelete();
            $table->foreignId('kontingen_id')->constrained('kontingens')->cascadeOnDelete();
            $table->enum('golongan', ['siaga', 'penggalang', 'penegak', 'pandega']);
            $table->unsignedInteger('nomor_urut_tampil')->nullable();
            $table->enum('status', ['draft', 'siap', 'selesai'])->default('draft');
            $table->timestamps();
            $table->unique(['lomba_id', 'kontingen_id', 'golongan'], 'lomba_kontingen_lomba_kontingen_golongan_unique');
            $table->index(['lomba_id', 'golongan']);
            $table->index(['kontingen_id', 'golongan']);
        });

        // --- penilaians ---
        Schema::create('penilaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lomba_id')->constrained('lombas')->cascadeOnDelete();
            $table->foreignId('kontingen_id')->constrained('kontingens')->cascadeOnDelete();
            $table->foreignId('juri_id')->constrained('users')->cascadeOnDelete();
            $table->enum('golongan', ['siaga', 'penggalang', 'penegak', 'pandega']);
            $table->unsignedInteger('nomor_urut_tampil')->nullable();
            $table->decimal('nilai_akhir_juri', 5, 2)->nullable();
            $table->boolean('is_locked')->default(true);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->unique(['lomba_id', 'kontingen_id', 'juri_id', 'golongan'], 'penilaians_lomba_kontingen_juri_golongan_unique');
            $table->index(['lomba_id', 'golongan']);
            $table->index(['kontingen_id', 'golongan']);
        });

        // --- penilaian_details (dibuat ulang biar FK ke penilaians rapi) ---
        Schema::create('penilaian_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penilaian_id')->constrained('penilaians')->cascadeOnDelete();
            $table->foreignId('kriteria_komponen_id')->constrained('kriteria_komponens')->cascadeOnDelete();
            $table->unsignedTinyInteger('nilai'); // validasi 1-100 di backend
            $table->timestamps();
            $table->unique(['penilaian_id', 'kriteria_komponen_id'], 'penilaian_details_penilaian_kriteria_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian_details');
        Schema::dropIfExists('penilaians');
        Schema::dropIfExists('lomba_kontingen');
        Schema::dropIfExists('juaras');
    }
};