<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verifikasi_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kontingen_id')
                ->constrained('kontingens')
                ->cascadeOnDelete();
            $table->string('item_type');
            $table->unsignedBigInteger('item_id');
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');
            $table->text('catatan')->nullable();
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->index(['item_type', 'item_id']);
            $table->unique(
                ['kontingen_id', 'item_type', 'item_id'],
                'verifikasi_items_kontingen_item_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifikasi_items');
    }
};