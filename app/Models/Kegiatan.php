<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    public function relawans()
    {
    return $this->hasMany(KegiatanRelawan::class, 'kegiatan_id');
    }
    
    protected $fillable = [
        'mosque_id',
        'tanggal',
        'nama',
        'kategori',
        'jam_mulai',
        'jam_selesai',
        'lokasi',
        'pemateri',
        'pj',
        'peserta',
        'status',
        'deskripsi'
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];
}
