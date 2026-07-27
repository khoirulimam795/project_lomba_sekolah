<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kriteria_komponens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lomba_id')
                ->constrained('lombas')
                ->cascadeOnDelete();

            // Kategori lomba = golongan Pramuka (sesuai permintaan client)
            $table->enum('golongan', ['siaga', 'penggalang', 'penegak', 'pandega']);

            $table->string('nama_komponen');
            $table->unsignedInteger('urutan')->default(1);   // integer, bukan string
            $table->boolean('is_active')->default(true);     // ada default

            $table->timestamps();

            $table->index(['lomba_id', 'golongan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kriteria_komponens');
    }
};