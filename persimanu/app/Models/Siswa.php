<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Siswa extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'kontingen_id',
        'nama',
        'nisn',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'nama_orang_tua',
        'alamat',
        'no_telp',
        'jenjang_pendidikan',
        'golongan_pramuka',
        'golongan_darah',
        'status_verifikasi',
        'catatan_verifikasi',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    /** 1 siswa = 1 surat keterangan sehat (upload baru = replace). */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('surat_kesehatan')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'application/pdf']);
    }

    public function kontingen(): BelongsTo
    {
        return $this->belongsTo(Kontingen::class);
    }
}