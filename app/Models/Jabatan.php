<?php

namespace App\Models;

use App\Models\Concerns\BelongsToMosque;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use BelongsToMosque;

    protected $fillable = ['mosque_id', 'organisasi', 'nama', 'parent_id', 'jamaah_id', 'urutan', 'posisi_x', 'posisi_y'];

    public function scopeForOrganisasi($query, string $organisasi)
    {
        return $query->where('organisasi', $organisasi);
    }

    public function jamaah()
    {
        return $this->belongsTo(Jamaah::class);
    }

    public function parent()
    {
        return $this->belongsTo(Jabatan::class, 'parent_id');
    }
}
