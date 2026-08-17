<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Inventaris extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'inventaris';

    protected $fillable = [
        'mosque_id',
        'nama',
        'kategori',
        'lokasi',
        'kondisi',
        'qty',
        'sumber',
        'kode',
        'tgl_beli',
        'harga',
        'catatan',
    ];

    protected $casts = [
        'tgl_beli' => 'date',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gambar')->singleFile();
    }
}