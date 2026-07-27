<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lomba_juri', function (Blueprint $table) {
        $table->id();
        $table->foreignId('lomba_id')
            ->constrained('lombas')
            ->cascadeOnDelete();
        $table->foreignId('juri_id')
            ->constrained('users')
            ->cascadeOnDelete();
        $table->timestamps();
        $table->unique(['lomba_id', 'juri_id']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lomba_juri');
    }
};
