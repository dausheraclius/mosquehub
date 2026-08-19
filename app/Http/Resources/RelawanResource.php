<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RelawanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'icon' => 'fa-users',
            'tanggal' => $this->tanggal->translatedFormat('d M Y'),
            'lokasi' => $this->lokasi ?: '-',
            'slotMax' => $this->slot_relawan_max ?? 20,
            'status' => $this->status === 'Akan Datang' ? 'Akan Datang' : $this->status,
            'relawan' => $this->relawans->map(fn ($r) => ['nama' => $r->nama, 'telepon' => $r->telepon])->values(),
        ];
    }
}
