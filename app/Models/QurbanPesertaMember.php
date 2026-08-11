<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QurbanPesertaMember extends Model
{
    protected $fillable = ['qurban_peserta_id', 'jamaah_id', 'nama'];

    public function peserta()
    {
        return $this->belongsTo(QurbanPeserta::class, 'qurban_peserta_id');
    }

    public function jamaah()
    {
        return $this->belongsTo(Jamaah::class, 'jamaah_id');
    }
}
