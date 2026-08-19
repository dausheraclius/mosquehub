<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgendaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tanggal' => $this->tanggal->format('Y-m-d'),
            'nama' => $this->nama,
            'kategori' => $this->kategori,
            'jamMulai' => $this->jam_mulai ? substr($this->jam_mulai, 0, 5) : '',
            'jamSelesai' => $this->jam_selesai ? substr($this->jam_selesai, 0, 5) : '',
            'lokasi' => $this->lokasi,
            'pemateri' => $this->pemateri,
            'pj' => $this->pj,
            'peserta' => $this->peserta,
            'status' => $this->status,
            'statusClass' => match ($this->status) {
                'Berlangsung' => 'status-berlangsung',
                'Selesai' => 'status-selesai',
                'Akan Datang' => 'status-akan-datang',
                'Dibatalkan' => 'status-nonaktif',
                default => 'status-nonaktif',
            },
            'deskripsi' => $this->deskripsi,
        ];
    }
}
