<?php

namespace App\Http\Controllers;

use App\Http\Resources\DonasiResource;
use App\Http\Resources\QurbanPesertaResource;
use App\Models\Donasi;
use App\Models\Jamaah;
use App\Models\QurbanPeserta;
use App\Support\Concerns\HasMosqueContext;

class InfaqSodaqohController extends Controller
{
    use HasMosqueContext;

    public function index()
    {
        $donasiList = Donasi::forMosque()
            ->orderByDesc('tanggal')
            ->get()
            ->map(fn ($d) => DonasiResource::make($d));

        $qurbanList = QurbanPeserta::forMosque()
            ->with('members', 'setorans')
            ->get()
            ->map(fn ($p) => QurbanPesertaResource::make($p));

        $petugasList = Jamaah::forMosque()
            ->where('status_jamaah', '!=', 'Wafat')
            ->whereNotNull('nama')
            ->orderBy('nama')
            ->pluck('nama')
            ->unique()
            ->values()
            ->all();

        $jamaahList = Jamaah::forMosque()
            ->where('status_jamaah', '!=', 'Wafat')
            ->whereNotNull('nama')
            ->orderBy('nama')
            ->get(['id', 'nama'])
            ->map(fn ($j) => ['id' => $j->id, 'nama' => $j->nama])
            ->values()
            ->all();

        return view('pages.keuangan.infaq-sodaqoh', [
            'donasiList' => $donasiList,
            'qurbanList' => $qurbanList,
            'petugasList' => $petugasList,
            'jamaahList' => $jamaahList,
        ]);
    }
}
