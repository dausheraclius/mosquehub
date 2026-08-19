<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QurbanPesertaResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'paket' => $this->paket,
            'is_patungan' => $this->paket === 'Patungan Sapi',
            'target' => (float) $this->target,
            'mulai' => $this->mulai->format('Y-m-d'),
            'members' => $this->members->map(fn ($m) => [
                'id' => $m->id,
                'jamaah_id' => $m->jamaah_id,
                'nama' => $m->nama,
            ])->values(),
            'riwayat' => $this->setorans->map(fn ($s) => [
                'id' => $s->id,
                'tanggal' => $s->tanggal->format('Y-m-d'),
                'jumlah' => (float) $s->jumlah,
                'metode' => $s->metode,
                'petugas' => $s->petugas,
                'member_id' => $s->qurban_peserta_member_id,
                'member_nama' => $s->member?->nama,
            ])->values(),
        ];
    }
}
