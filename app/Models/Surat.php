<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Surat extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'mosque_id',
        'nomor',
        'subjek',
        'jenis',
        'status',
        'tanggal',
        'kepada',
        'isi',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('lampiran')->singleFile();
    }
}