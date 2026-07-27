<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenilaianDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'penilaian_id',
        'kriteria_komponen_id',
        'nilai',
    ];

    protected $casts = [
        'nilai' => 'integer',
    ];

    public function penilaian(): BelongsTo
    {
        return $this->belongsTo(Penilaian::class);
    }

    public function kriteria(): BelongsTo
    {
        return $this->belongsTo(KriteriaKomponen::class, 'kriteria_komponen_id');
    }
}