<?php

namespace App\Http\Controllers;

use App\Models\GaleriAlbum;
use App\Support\MediaHelper;
use App\Support\SiteContext;

class PublicGaleriController extends Controller
{
    public function index()
    {
        $albums = GaleriAlbum::forMosque(SiteContext::mosqueId())
            ->where('status', 'published')
            ->orderByDesc('tanggal')
            ->get()
            ->map(fn (GaleriAlbum $a) => [
                'id' => $a->id,
                'nama' => $a->nama,
                'tanggal' => $a->tanggal->translatedFormat('d F Y'),
                'deskripsi' => $a->deskripsi,
                'cover' => $this->coverOf($a),
                'photoCount' => $a->getMedia('photos')->count(),
            ]);

        return view('pages.public.galeri', ['albums' => $albums]);
    }

    public function album(GaleriAlbum $album)
    {
        $mosqueId = SiteContext::mosqueId();
        abort_unless($album->mosque_id === $mosqueId && $album->status === 'published', 404);

        return view('pages.public.galeri-album', [
            'album' => $album,
            'photos' => collect($album->photosForFrontend()),
        ]);
    }

    private function coverOf(GaleriAlbum $a): ?string
    {
        $photos = $a->getMedia('photos');
        if ($photos->isEmpty()) {
            return null;
        }

        $cover = $photos->first(fn ($m) => (bool) $m->getCustomProperty('is_cover', false)) ?? $photos->first();

        return MediaHelper::relativeUrl($cover->getUrl());
    }
}