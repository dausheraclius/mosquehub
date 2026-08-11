<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mosque extends Model
{
    protected $fillable = [
        'name', 'short_name', 'established_year', 'category', 'phone', 'email',
        'website', 'province', 'city', 'district', 'kelurahan', 'postal_code',
        'address', 'maps_link', 'instagram', 'facebook', 'youtube', 'tiktok',
        'whatsapp', 'status',
    ];
}