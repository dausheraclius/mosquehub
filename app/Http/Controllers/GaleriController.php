<?php

namespace App\Http\Controllers;

use App\Models\GaleriAlbum;
use App\Support\Concerns\HasMosqueContext;
use App\Support\MediaHelper;
use Illuminate\Http\Request;

class GaleriController extends Controller
{
    use HasMosqueContext;

    public function index()
    {
        $albums = GaleriAlbum::forMosque()
            ->orderByDesc('tanggal')
            ->get()
            ->map(fn ($a) => $this->toArray($a));

        return view('pages.kegiatan.galeri', ['albumList' => $albums]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $validated['mosque_id'] = $this->mosqueId;

        $album = GaleriAlbum::create($validated);

        return response()->json($this->toArray($album));
    }

    public function update(Request $request, int $albumId)
    {
        $album = $this->findAlbum($albumId);

        $validated = $request->validate($this->rules());

        $album->update($validated);

        return response()->json($this->toArray($album));
    }

    public function destroy(Request $request, int $albumId)
    {
        $album = $this->findAlbum($albumId);
        $album->delete(); // otomatis ikut hapus semua fotonya (bawaan Media Library)

        return response()->json(['success' => true]);
    }

    public function uploadPhotos(Request $request, int $albumId)
    {
        $album = $this->findAlbum($albumId);
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
                'url' => MediaHelper::relativeUrl($media->getUrl()),
                'isCover' => (bool) $media->getCustomProperty('is_cover', false),
            ];
        }

        return response()->json($newPhotos);
    }

    public function setCoverPhoto(Request $request, int $albumId, int $mediaId)
    {
        $album = $this->findAlbum($albumId);
        foreach ($album->getMedia('photos') as $m) {
            $m->setCustomProperty('is_cover', $m->id === $mediaId)->save();
        }

        return response()->json(['success' => true]);
    }

    public function deletePhoto(Request $request, int $albumId, int $mediaId)
    {
        $album = $this->findAlbum($albumId);
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

    public function togglePublish(Request $request, int $albumId)
    {
        $album = $this->findAlbum($albumId);
        $validated = $request->validate(['published' => 'required|boolean']);
        $album->update(['status' => $validated['published'] ? 'published' : 'draft']);

        return response()->json(['success' => true]);
    }

    private function findAlbum(int $albumId): GaleriAlbum
    {
        return GaleriAlbum::forMosque()->findOrFail($albumId);
    }

    private function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'status' => 'required|in:draft,published',
            'deskripsi' => 'nullable|string',
        ];
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