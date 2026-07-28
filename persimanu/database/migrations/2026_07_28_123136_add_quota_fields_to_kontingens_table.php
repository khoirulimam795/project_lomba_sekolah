<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kontingens', function (Blueprint $table) {

            // Kuota pendamping & peserta per gender
            // Default 0 agar kontingen lama tidak error
            $table->unsignedInteger('pendamping_putra')->default(0)->after('contact_phone');
            $table->unsignedInteger('pendamping_putri')->default(0)->after('pendamping_putra');
            $table->unsignedInteger('peserta_putra')->default(0)->after('pendamping_putri');
            $table->unsignedInteger('peserta_putri')->default(0)->after('peserta_putra');
        });
    }

    public function down(): void
    {
        Schema::table('kontingens', function (Blueprint $table) {
            $table->dropColumn([
                'nama_kepala_madrasah',
                'contact_person',
                'contact_phone',
                'pendamping_putra',
                'pendamping_putri',
                'peserta_putra',
                'peserta_putri',
            ]);
        });
    }
};