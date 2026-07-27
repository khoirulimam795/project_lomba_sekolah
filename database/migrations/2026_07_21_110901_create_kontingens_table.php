<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kontingens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete(); // team = pangkalan

            // Status alur registrasi (sesuai PRD 4.1)
            $table->enum('status', [
                'draft',                          // kesediaan diisi, belum upload bayar
                'menunggu_approval_pembayaran',  // bukti bayar diupload, nunggu admin
                'pembayaran_ditolak',            // admin reject (bisa upload ulang)
                'menunggu_verifikasi_dokumen',   // bayar approved, bisa isi biodata
                'verifikasi_ditolak',            // ada item dokumen ditolak
                'terverifikasi',                 // semua approved → final
            ])->default('draft');

            // Data kesediaan (C.01)
            $table->string('nama_kontingen')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_phone')->nullable();

            // Untuk approval pembayaran (diisi Admin di B2)
            $table->text('catatan_pembayaran')->nullable(); // alasan reject
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('finalized_at')->nullable();

            $table->timestamps();

            // 1 pangkalan cuma bisa daftar 1x per event
            $table->unique(['event_id', 'team_id'], 'kontingens_event_team_unique');
            $table->index(['team_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kontingens');
    }
};