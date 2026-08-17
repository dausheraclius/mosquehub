<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\PengaturanUmum;
use App\Support\SiteContext;

class PublicLandingController extends Controller
{
    public function index()
    {
        $activities = Kegiatan::where('mosque_id', SiteContext::mosqueId())
            ->where('tanggal', '>=', now()->toDateString())
            ->orderBy('tanggal')
            ->take(8)
            ->get()
            ->map(fn ($k) => [
                'title' => $k->nama,
                'date' => $k->tanggal->translatedFormat('d F Y') . ($k->jam_mulai ? ' - ' . substr($k->jam_mulai, 0, 5) : ''),
                'location' => $k->lokasi ?: '-',
                'desc' => $k->deskripsi ?: '-',
            ]);

        $pengaturan = PengaturanUmum::where('mosque_id', SiteContext::mosqueId())->first();
        $sholatJadwal = $pengaturan?->sholatJadwal() ?? (new PengaturanUmum)->sholatJadwal();

        return view('pages.public.landing', [
            'activities' => $activities,
            'sholatJadwal' => $sholatJadwal,
        ]);
    }
}
