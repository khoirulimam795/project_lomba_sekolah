<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('juaras', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_id')
                ->constrained('events')
                ->cascadeOnDelete();
            $table->foreignId('lomba_id')
                ->constrained('lombas')
                ->cascadeOnDelete();
            $table->foreignId('kontingen_id')
                ->constrained('kontingens')
                ->cascadeOnDelete();
            $table->enum('golongan', ['SD', 'SMP', 'SMA']);
            $table->unsignedTinyInteger('juara');
            $table->enum('medali', ['emas', 'perak', 'perunggu']);
            $table->decimal('nilai_akhir', 5, 2)->default(0);
            $table->boolean('is_final')->default(false);
            $table->timestamps();
            // 1 sekolah hanya bisa dapat 1 juara pada 1 lomba & golongan yang sama
            $table->unique(
                ['lomba_id', 'kontingen_id', 'golongan'],
                'juaras_lomba_kontingen_golongan_unique'
            );
            $table->index(['event_id', 'golongan']);
            $table->index(['event_id', 'medali']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('juaras');
    }
};