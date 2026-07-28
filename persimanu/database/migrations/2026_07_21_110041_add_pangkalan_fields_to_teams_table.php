<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
        $table->string('npsn')->nullable()->after('name');
        $table->enum('jenjang', [
            'SD',
            'MI',
            'SMP',
            'MTs',
            'SMA',
            'MA',
            'SMK'
        ])->nullable()->after('npsn');

        $table->text('alamat')->nullable()->after('jenjang');
        $table->string('no_telp')->nullable()->after('alamat');
        $table->string('logo_path')->nullable()->after('no_telp');
});
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn([
                'npsn',
                'jenjang',
                'alamat',
                'no_telp',
                'logo_path',
            ]);
        });
    }
};