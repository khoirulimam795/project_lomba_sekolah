<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kontingens')) return;

        Schema::table('kontingens', function (Blueprint $table) {
            if (!Schema::hasColumn('kontingens', 'nama_kepala_madrasah')) {
                $table->string('nama_kepala_madrasah')->nullable()->after('nama_kontingen');
            }
            if (!Schema::hasColumn('kontingens', 'asal_instansi')) {
                $table->string('asal_instansi')->nullable()->after('nama_kepala_madrasah');
            }
            if (!Schema::hasColumn('kontingens', 'contact_person')) {
                $table->string('contact_person')->nullable()->after('asal_instansi');
            }
            if (!Schema::hasColumn('kontingens', 'contact_phone')) {
                $table->string('contact_phone')->nullable()->after('contact_person');
            }
            if (!Schema::hasColumn('kontingens', 'pendamping_putra')) {
                $table->unsignedInteger('pendamping_putra')->default(0)->after('contact_phone');
            }
            if (!Schema::hasColumn('kontingens', 'pendamping_putri')) {
                $table->unsignedInteger('pendamping_putri')->default(0)->after('pendamping_putra');
            }
            if (!Schema::hasColumn('kontingens', 'peserta_putra')) {
                $table->unsignedInteger('peserta_putra')->default(0)->after('pendamping_putri');
            }
            if (!Schema::hasColumn('kontingens', 'peserta_putri')) {
                $table->unsignedInteger('peserta_putri')->default(0)->after('peserta_putra');
            }
            if (!Schema::hasColumn('kontingens', 'catatan_pembayaran')) {
                $table->text('catatan_pembayaran')->nullable()->after('peserta_putri');
            }
        });
    }

    public function down(): void
    {
        // no-op
    }
};