<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::create('lomba_kontingen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lomba_id')->constrained()->onDelete('cascade');
            $table->foreignId('kontingen_id')->constrained()->onDelete('cascade');
            $table->enum('jenjang_pendidikan',['SD','MI','SMP','MTS','SMA','MA','SMK'])->nullable();
            $table->enum('golongan_pramuka',['siaga','penegak','penggalang','pandega'])->nullable();
            $table->enum('status',['draft','siap','selesai'])->default('draft');
            $table->timestamps();
        });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('lomba_kontingen');
    }
};
