<?php

namespace App\Http\Resources;

use App\Support\MediaHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventarisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'kategori' => $this->kategori,
            'lokasi' => $this->lokasi,
            'kondisi' => $this->kondisi,
            'qty' => $this->qty,
            'sumber' => $this->sumber,
            'kode' => $this->kode,
            'tglBeli' => $this->tgl_beli?->format('d M Y'),
            'tglBeliIso' => $this->tgl_beli?->format('Y-m-d'),
            'harga' => $this->harga,
            'catatan' => $this->catatan,
            'gambar' => MediaHelper::relativeUrl($this->getFirstMediaUrl('gambar') ?: null),
        ];
    }
}
