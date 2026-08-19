<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Mosque extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name', 'short_name', 'established_year', 'category', 'phone', 'email',
        'website', 'province', 'city', 'district', 'kelurahan', 'postal_code',
        'address', 'maps_link', 'instagram', 'facebook', 'youtube', 'tiktok',
        'whatsapp', 'status',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function registerMediaCollections(): void
    {
    $this->addMediaCollection('logo')->singleFile();
    $this->addMediaCollection('stempel')->singleFile();
    $this->addMediaCollection('kop_surat')->singleFile();
    $this->addMediaCollection('ttd')->singleFile();
    }
}