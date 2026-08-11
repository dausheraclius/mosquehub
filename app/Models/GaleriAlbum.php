<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class GaleriAlbum extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['mosque_id', 'nama', 'tanggal', 'deskripsi', 'status'];

    protected $casts = ['tanggal' => 'date'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos');
    }

    public function photosForFrontend(): array
    {
        return $this->getMedia('photos')->map(function (Media $m) {
            return [
                'id' => $m->id,
                'url' => $m->getUrl(),
                'isCover' => (bool) $m->getCustomProperty('is_cover', false),
            ];
        })->values()->toArray();
    }
}