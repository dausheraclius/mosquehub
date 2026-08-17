<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPetugasSholat extends Model
{
    use HasFactory;

    protected $fillable = [
        'mosque_id',
        'tanggal',
        'sholat',
        'khatib',
        'imam',
        'muadzin',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }
}