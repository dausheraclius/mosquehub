<?php

namespace App\Http\Controllers;

use App\Models\JadwalPetugasSholat;
use App\Support\SiteContext;

class PublicPetugasSholatController extends Controller
{
    public function index()
    {
        $mosqueId = SiteContext::mosqueId();

        $items = JadwalPetugasSholat::where('mosque_id', $mosqueId)
            ->where('tanggal', '>=', now()->toDateString())
            ->orderBy('tanggal')
            ->orderByRaw('FIELD(sholat, "Subuh", "Dzuhur", "Ashar", "Maghrib", "Isya")')
            ->take(60)
            ->get()
            ->groupBy(fn ($p) => $p->tanggal->format('Y-m-d'))
            ->map(fn ($group) => [
                'label' => $group->first()->tanggal->translatedFormat('l, d F Y'),
                'rows' => $group->map(fn ($p) => [
                    'sholat' => $p->sholat,
                    'khatib' => $p->khatib ?: null,
                    'imam' => $p->imam ?: null,
                    'muadzin' => $p->muadzin ?: null,
                    'keterangan' => $p->keterangan ?: null,
                ])->values(),
            ])
            ->values();

        return view('pages.public.jadwal-petugas', [
            'items' => $items,
        ]);
    }
}
