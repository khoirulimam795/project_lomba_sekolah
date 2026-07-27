<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lomba_id')
                ->constrained('lombas')
                ->cascadeOnDelete();
            $table->foreignId('kontingen_id')
                ->constrained('kontingens')
                ->cascadeOnDelete();
            $table->foreignId('juri_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->enum('golongan', ['SD', 'SMP', 'SMA']);
            $table->unsignedInteger('nomor_urut_tampil')->nullable();
            $table->decimal('nilai_akhir_juri', 5, 2)->nullable();
            $table->boolean('is_locked')->default(true);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            // Unique constraint rekomendasi:
            // 1 juri hanya bisa submit 1x untuk 1 sekolah, 1 lomba, 1 golongan
            $table->unique(
                ['lomba_id', 'kontingen_id', 'juri_id', 'golongan'],
                'penilaians_lomba_kontingen_juri_golongan_unique'
            );
            $table->index(['lomba_id', 'golongan']);
            $table->index(['kontingen_id', 'golongan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaians');
    }
};