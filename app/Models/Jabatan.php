<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $fillable = ['mosque_id', 'nama', 'parent_id', 'jamaah_id', 'urutan', 'posisi_x', 'posisi_y'];

    public function jamaah()
    {
        return $this->belongsTo(Jamaah::class);
    }

    public function parent()
    {
        return $this->belongsTo(Jabatan::class, 'parent_id');
    }
}   