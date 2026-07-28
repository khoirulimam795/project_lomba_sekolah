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
        Schema::create('penilaian_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penilaian_id')
                ->constrained('penilaians')
                ->cascadeOnDelete();
            $table->foreignId('kriteria_komponen_id')
                ->constrained('kriteria_komponens')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('nilai');
            $table->timestamps();
            // 1 penilaian tidak boleh punya komponen yang duplikat
            $table->unique(
                ['penilaian_id', 'kriteria_komponen_id'],
                'penilaian_details_penilaian_kriteria_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian_details');
    }
};
