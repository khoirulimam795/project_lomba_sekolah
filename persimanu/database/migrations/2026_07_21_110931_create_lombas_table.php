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
        Schema::create('lombas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
        $table->string('nama');
        $table->string('slug')->unique();
        $table->text('deskripsi')->nullable();
        $table->enum('status', ['draft', 'aktif', 'selesai'])->default('draft');
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lombas');
    }
};
