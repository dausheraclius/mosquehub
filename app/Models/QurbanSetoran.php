<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QurbanSetoran extends Model
{
    protected $fillable = [
        'qurban_peserta_id', 'qurban_peserta_member_id', 'tanggal', 'jumlah', 'metode', 'petugas',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
    ];

    public function peserta()
    {
        return $this->belongsTo(QurbanPeserta::class, 'qurban_peserta_id');
    }

    public function member()
    {
        return $this->belongsTo(QurbanPesertaMember::class, 'qurban_peserta_member_id');
    }
}