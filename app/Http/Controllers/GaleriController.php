<?php

namespace App\Http\Controllers;

use App\Models\GaleriAlbum;
use Illuminate\Http\Request;

class GaleriController extends Controller
{
    private int $mosqueId = 1; // TODO: ganti setelah Auth dibikin

    public function index()
    {
        $albums = GaleriAlbum::where('mosque_id', $this->mosqueId)
            ->orderByDesc('tanggal')
            ->get()
            ->map(fn ($a) => $this->toArray($a));

        return view('pages.kegiatan.galeri', ['albumList' => $albums]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'status' => 'required|in:draft,published',
            'deskripsi' => 'nullable|string',
        ]);
        $validated['mosque_id'] = $this->mosqueId;

        $album = GaleriAlbum::create($validated);

        return response()->json($this->toArray($album));
    }

    public function update(Request $request, GaleriAlbum $album)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'status' => 'required|in:draft,published',
            'deskripsi' => 'nullable|string',
        ]);

        $album->update($validated);

        return response()->json($this->toArray($album));
    }

    public function destroy(GaleriAlbum $album)
    {
        $album->delete(); // otomatis ikut hapus semua fotonya (bawaan Media Library)

        return response()->json(['success' => true]);
    }

    public function uploadPhotos(Request $request, GaleriAlbum $album)
    {
        $request->validate([
            'photos' => 'required|array',
            'photos.*' => 'image|max:5120', // maks 5MB per foto
        ]);

        $isFirstBatch = $album->getMedia('photos')->isEmpty();

        $newPhotos = [];
        foreach ($request->file('photos') as $index => $file) {
            $media = $album->addMedia($file)->toMediaCollection('photos');
            if ($isFirstBatch && $index === 0) {
                $media->setCustomProperty('is_cover', true)->save();
            }
            $newPhotos[] = [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'isCover' => (bool) $media->getCustomProperty('is_cover', false),
            ];
        }

        return response()->json($newPhotos);
    }

    public function setCoverPhoto(GaleriAlbum $album, int $mediaId)
    {
        foreach ($album->getMedia('photos') as $m) {
            $m->setCustomProperty('is_cover', $m->id === $mediaId)->save();
        }

        return response()->json(['success' => true]);
    }

    public function deletePhoto(GaleriAlbum $album, int $mediaId)
    {
        $media = $album->getMedia('photos')->firstWhere('id', $mediaId);
        $wasCover = $media && (bool) $media->getCustomProperty('is_cover', false);
        $media?->delete();

        $newCoverId = null;
        if ($wasCover) {
            $first = $album->getMedia('photos')->first();
            if ($first) {
                $first->setCustomProperty('is_cover', true)->save();
                $newCoverId = $first->id;
            }
        }

        return response()->json(['success' => true, 'newCoverId' => $newCoverId]);
    }

    public function togglePublish(Request $request, GaleriAlbum $album)
    {
        $validated = $request->validate(['published' => 'required|boolean']);
        $album->update(['status' => $validated['published'] ? 'published' : 'draft']);

        return response()->json(['success' => true]);
    }

    private function toArray(GaleriAlbum $a): array
    {
        return [
            'id' => $a->id,
            'nama' => $a->nama,
            'tanggal' => $a->tanggal->format('Y-m-d'),
            'deskripsi' => $a->deskripsi,
            'status' => $a->status,
            'photos' => $a->photosForFrontend(),
        ];
    }
}