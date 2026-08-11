<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    protected $table = 'pengumumans';

    protected $fillable = ['mosque_id', 'judul', 'isi', 'kategori', 'status', 'tanggal'];

    protected $casts = ['tanggal' => 'date'];
}