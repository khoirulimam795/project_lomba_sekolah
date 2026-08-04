<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LombaKontingen extends Model
{
    use HasFactory;

    protected $table = 'lomba_kontingen';

    protected $fillable = [
        'lomba_id',
        'kontingen_id',
        'golongan',
        'pendamping_id',
        'nomor_urut_tampil',
        'kategori',
        'status',
    ];

    public function lomba(): BelongsTo
    {
        return $this->belongsTo(Lomba::class);
    }

    public function kontingen(): BelongsTo
    {
        return $this->belongsTo(Kontingen::class);
    }

    public function pendamping(): BelongsTo
    {
        return $this->belongsTo(Pendamping::class);
    }

    /** Siswa yang dialokasikan ke alokasi ini (max 10, di-enforce di controller). */
    public function siswas(): BelongsToMany
    {
        return $this->belongsToMany(
            Siswa::class,
            'lomba_kontingen_siswa',
            'lomba_kontingen_id',
            'siswa_id'
        )->withPivot('id');
    }
}