<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KriteriaKomponen extends Model
{
    use HasFactory;

    protected $fillable = [
        'lomba_id',
        'golongan',
        'nama_komponen',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function lomba(): BelongsTo
    {
        return $this->belongsTo(Lomba::class);
    }

    public function penilaianDetails(): HasMany
    {
        return $this->hasMany(PenilaianDetail::class);
    }
}