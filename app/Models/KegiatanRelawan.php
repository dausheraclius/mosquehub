<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KegiatanRelawan extends Model
{
    public function kegiatan()
    {
    return $this->belongsTo(Kegiatan::class);
    }
    
    protected $table = 'kegiatan_relawan';

    protected $fillable = ['kegiatan_id', 'nama', 'telepon'];
}