<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonasiResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tanggal' => $this->tanggal->format('d/m/Y'),
            'tanggal_akhir' => $this->tanggal_akhir ? $this->tanggal_akhir->format('d/m/Y') : null,
            'donatur' => $this->donatur,
            'kategori' => $this->kategori,
            'jenis' => $this->jenis,
            'tipe' => $this->tipe,
            'nominal' => (float) $this->nominal,
            'keterangan' => $this->keterangan ?: '-',
            'metode' => $this->metode ?: '-',
            'petugas' => $this->petugas,
            'status' => $this->status,
        ];
    }
}
