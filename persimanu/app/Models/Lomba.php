<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lomba extends Model
{
    use HasFactory;

    protected $fillable = [
    'event_id',
    'nama',
    'slug',
    'deskripsi',
    'golongan',   // ✅ baru
    'kategori',   // ✅ baru
    'status',
    'created_by',
];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function kriterias(): HasMany
    {
        return $this->hasMany(KriteriaKomponen::class);
    }

    public function kriteriaKomponens(): HasMany
{
    return $this->hasMany(KriteriaKomponen::class)->orderBy('urutan');
}

    public function juri(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'lomba_juri', 'lomba_id', 'juri_id');
    }

    public function kontingen(): BelongsToMany
    {
        return $this->belongsToMany(Kontingen::class, 'lomba_kontingen', 'lomba_id', 'kontingen_id');
    }

    public function alokasi()
    {
        return $this->hasMany(LombaKontingen::class);
    }

    public function penilaians()
    {
        return $this->hasMany(Penilaian::class);
    }

    public function juaras()
    {
        return $this->hasMany(Juara::class);
    }
}
