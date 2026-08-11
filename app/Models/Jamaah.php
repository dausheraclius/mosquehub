<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jamaah extends Model
{
    protected $table = 'jamaah';

    protected $fillable = [
        'mosque_id',
        'nama',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'no_hp',
        'email',
        'alamat',
        'pekerjaan',
        'status_pernikahan',
        'status_jamaah',
        'tanggal_bergabung',
        'catatan',
        'foto',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_bergabung' => 'date',
    ];
}