<?php

namespace App\Models;

use App\Models\Concerns\BelongsToMosque;
use Illuminate\Database\Eloquent\Model;

class KasTransaction extends Model
{
    use BelongsToMosque;

    protected $fillable = [
        'mosque_id', 'tanggal', 'jenis', 'kategori', 'keterangan',
        'pemasukan', 'pengeluaran', 'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'pemasukan' => 'decimal:2',
        'pengeluaran' => 'decimal:2',
    ];
}