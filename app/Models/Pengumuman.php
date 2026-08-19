<?php

namespace App\Models;

use App\Models\Concerns\BelongsToMosque;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    use BelongsToMosque;

    protected $table = 'pengumumans';

    protected $fillable = ['mosque_id', 'judul', 'isi', 'kategori', 'status', 'tanggal'];

    protected $casts = ['tanggal' => 'date'];
}