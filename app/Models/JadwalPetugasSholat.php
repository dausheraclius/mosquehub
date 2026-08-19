<?php

namespace App\Models;

use App\Models\Concerns\BelongsToMosque;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPetugasSholat extends Model
{
    use BelongsToMosque, HasFactory;

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