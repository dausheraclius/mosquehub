<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QurbanSetoranResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'peserta_id' => $this->qurban_peserta_id,
            'member_id' => $this->qurban_peserta_member_id,
            'member_nama' => $this->member?->nama,
            'tanggal' => $this->tanggal->format('Y-m-d'),
            'jumlah' => (float) $this->jumlah,
            'metode' => $this->metode,
            'petugas' => $this->petugas,
        ];
    }
}
