<?php

namespace App\Models;

use App\Models\Concerns\BelongsToMosque;
use Illuminate\Database\Eloquent\Model;

class QurbanPeserta extends Model
{
    use BelongsToMosque;

    protected $fillable = ['mosque_id', 'nama', 'paket', 'target', 'mulai'];

    protected $casts = [
        'mulai' => 'date',
        'target' => 'decimal:2',
    ];

    public function setorans()
    {
        return $this->hasMany(QurbanSetoran::class);
    }

    public function members()
    {
        return $this->hasMany(QurbanPesertaMember::class);
    }
}