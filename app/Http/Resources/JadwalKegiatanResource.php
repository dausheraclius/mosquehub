<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\FormatTanggalIndonesia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class JadwalKegiatanResource extends JsonResource
{
    use FormatTanggalIndonesia;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tanggal' => $this->tanggal->format('Y-m-d'),
            'hari' => $this->hariIndonesia($this->tanggal->format('l')),
            'tanggalLabel' => $this->tanggalLabel($this->tanggal),
            'nama' => $this->nama,
            'lokasi' => $this->lokasi,
            'lokasiSlug' => $this->lokasi ? Str::slug($this->lokasi) : '',
            'pemateri' => $this->pemateri ?: $this->pj,
            'jamMulai' => $this->jam_mulai ? substr($this->jam_mulai, 0, 5) : '-',
            'jamSelesai' => $this->jam_selesai ? substr($this->jam_selesai, 0, 5) : '-',
            'jamMulaiDot' => $this->jam_mulai ? str_replace(':', '.', substr($this->jam_mulai, 0, 5)) : '-',
            'kategoriSlug' => Str::slug($this->kategori),
            'kategori' => $this->kategori,
            'statusSlug' => Str::slug($this->status),
            'status' => $this->status,
            'deskripsi' => $this->deskripsi,
            'peserta' => $this->peserta,
        ];
    }
}
