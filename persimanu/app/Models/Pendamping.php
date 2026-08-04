<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pendamping extends Model
{
    use HasFactory;

    protected $fillable = [
        'kontingen_id',
        'slot_index',
        'nama',
        'jenis_kelamin',
        'jabatan',
        'pekerjaan',
        'golongan_binaan',
        'asal_instansi',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'no_telp',
        'kota',
        'golongan_darah',
        'status_verifikasi',
        'catatan_verifikasi',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function kontingen(): BelongsTo
    {
        return $this->belongsTo(Kontingen::class);
    }

    public function alokasi()
    {
        return $this->hasMany(LombaKontingen::class);
    }
}