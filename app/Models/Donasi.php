<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donasi extends Model
{
    protected $fillable = [
        'mosque_id', 'tanggal', 'tanggal_akhir', 'donatur', 'kategori', 'jenis',
        'tipe', 'nominal', 'keterangan', 'metode', 'petugas', 'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_akhir' => 'date',
        'nominal' => 'decimal:2',
    ];
}