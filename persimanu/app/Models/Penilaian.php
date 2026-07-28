<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penilaian extends Model
{
    use HasFactory;

    protected $fillable = [
        'lomba_id',
        'kontingen_id',
        'juri_id',
        'golongan',
        'nomor_urut_tampil',
        'nilai_akhir_juri',
        'is_locked',
        'submitted_at',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'nilai_akhir_juri' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    public function lomba(): BelongsTo
    {
        return $this->belongsTo(Lomba::class);
    }

    public function kontingen(): BelongsTo
    {
        return $this->belongsTo(Kontingen::class);
    }

    public function juri(): BelongsTo
    {
        return $this->belongsTo(User::class, 'juri_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PenilaianDetail::class);
    }
}