<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KasTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tanggal' => $this->tanggal->format('d/m/y'),
            'tanggalIso' => $this->tanggal->format('Y-m-d'),
            'jenis' => $this->jenis,
            'kategori' => $this->kategori,
            'ket' => $this->keterangan,
            'masuk' => (float) $this->pemasukan,
            'keluar' => (float) $this->pengeluaran,
            'tipe' => (float) $this->pemasukan > 0 ? 'Pemasukan' : 'Pengeluaran',
            'oleh' => $this->dibuat_oleh,
        ];
    }
}
