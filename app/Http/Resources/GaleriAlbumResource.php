<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GaleriAlbumResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'tanggal' => $this->tanggal->format('Y-m-d'),
            'deskripsi' => $this->deskripsi,
            'status' => $this->status,
            'photos' => $this->photosForFrontend(),
        ];
    }
}
