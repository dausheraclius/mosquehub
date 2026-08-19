<?php

namespace App\Models;

use App\Models\Concerns\BelongsToMosque;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KontakPesan extends Model
{
    use BelongsToMosque, HasFactory;

    protected $fillable = [
        'mosque_id',
        'nama',
        'email',
        'telepon',
        'subjek',
        'pesan',
        'status',
    ];
}