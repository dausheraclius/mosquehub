<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\FormatTanggalIndonesia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JadwalPetugasResource extends JsonResource
{
    use FormatTanggalIndonesia;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tanggal' => $this->tanggal->format('Y-m-d'),
            'hari' => $this->hariIndonesia($this->tanggal->format('l')),
            'sholat' => $this->sholat,
            'khatib' => $this->khatib,
            'imam' => $this->imam,
            'muadzin' => $this->muadzin,
            'keterangan' => $this->keterangan,
            'isJumat' => $this->sholat === 'Jumat',
            'lewat' => $this->tanggal->lt(now()->startOfDay()),
        ];
    }
}
