<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'slug',
        'deskripsi',
        'periode_pendaftaran_mulai',
        'periode_pendaftaran_selesai',
        'tanggal_pelaksanaan_mulai',
        'tanggal_pelaksanaan_selesai',
        'status',
        'created_by',
    ];

    protected $casts = [
        'periode_pendaftaran_mulai' => 'date',
        'periode_pendaftaran_selesai' => 'date',
        'tanggal_pelaksanaan_mulai' => 'date',
        'tanggal_pelaksanaan_selesai' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lombas(): HasMany
    {
        return $this->hasMany(Lomba::class);
    }

    public function kontingens(): HasMany
    {
        return $this->hasMany(Kontingen::class);
    }

    public function juaras(): HasMany
    {
        return $this->hasMany(Juara::class);
    }
}