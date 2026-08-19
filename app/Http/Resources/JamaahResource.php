<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\FormatTanggalIndonesia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JamaahResource extends JsonResource
{
    use FormatTanggalIndonesia;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'gender' => $this->jenis_kelamin === 'Laki-laki' ? 'Laki-laki / Ikhwan' : 'Perempuan / Akhwat',
            'hp' => $this->no_hp,
            'email' => $this->email,
            'status' => $this->status_jamaah,
            'tempatLahir' => $this->tempat_lahir,
            'tanggalLahir' => $this->formatTanggalLahir($this->tanggal_lahir),
            'tanggalLahirIso' => $this->tanggal_lahir?->format('Y-m-d'),
            'alamat' => $this->alamat,
            'pekerjaan' => $this->pekerjaan,
            'statusPernikahan' => $this->status_pernikahan,
            'tanggalBergabung' => $this->formatTanggalBergabung($this->tanggal_bergabung),
            'tanggalBergabungIso' => $this->tanggal_bergabung?->format('Y-m-d'),
            'foto' => $this->foto ? '/storage/'.$this->foto : null,
            'catatan' => $this->catatan ?: '-',
        ];
    }
}
