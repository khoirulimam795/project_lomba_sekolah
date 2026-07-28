<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Juara extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'lomba_id',
        'kontingen_id',
        'golongan',
        'juara',
        'medali',
        'nilai_akhir',
        'is_final',
    ];

    protected $casts = [
        'nilai_akhir' => 'decimal:2',
        'is_final' => 'boolean',
    ];

    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function lomba(): BelongsTo { return $this->belongsTo(Lomba::class); }
    public function kontingen(): BelongsTo { return $this->belongsTo(Kontingen::class); }
}