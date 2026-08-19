<?php

namespace App\Http\Resources;

use App\Support\MediaHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SuratResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nomor' => $this->nomor,
            'subjek' => $this->subjek,
            'jenis' => $this->jenis,
            'status' => $this->status,
            'tanggal' => $this->tanggal->format('Y-m-d'),
            'kepada' => $this->kepada,
            'isi' => $this->isi,
            'fileUrl' => MediaHelper::relativeUrl($this->getFirstMediaUrl('lampiran') ?: null),
            'fileName' => $this->getFirstMedia('lampiran')?->file_name,
        ];
    }
}
