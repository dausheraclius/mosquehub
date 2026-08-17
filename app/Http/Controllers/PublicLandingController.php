<?php

namespace App\Http\Controllers;

use App\Models\Donasi;
use App\Models\Jamaah;
use App\Models\KasTransaction;
use App\Models\Kegiatan;
use App\Models\KegiatanRelawan;
use App\Models\PengaturanUmum;
use App\Support\SiteContext;

class PublicLandingController extends Controller
{
    public function index()
    {
        $mosqueId = SiteContext::mosqueId();

        $activities = Kegiatan::where('mosque_id', $mosqueId)
            ->where('tanggal', '>=', now()->toDateString())
            ->orderBy('tanggal')
            ->take(8)
            ->get()
            ->map(fn ($k) => [
                'id' => $k->id,
                'title' => $k->nama,
                'date' => $k->tanggal->translatedFormat('d F Y') . ($k->jam_mulai ? ' - ' . substr($k->jam_mulai, 0, 5) : ''),
                'location' => $k->lokasi ?: '-',
                'desc' => $k->deskripsi ?: '-',
            ]);

        $pengaturan = PengaturanUmum::where('mosque_id', $mosqueId)->first();
        $sholatJadwal = $pengaturan?->sholatJadwal() ?? (new PengaturanUmum)->sholatJadwal();

        // Ringkasan transparansi — sama seperti di dashboard admin.
        $kas = KasTransaction::where('mosque_id', $mosqueId)->get();
        $saldoKas = (float) $kas->sum('pemasukan') - (float) $kas->sum('pengeluaran');

        $stats = [
            'totalJemaah' => Jamaah::where('mosque_id', $mosqueId)->count(),
            'saldoKas' => $saldoKas,
            'donasiBulanIni' => (float) Donasi::where('mosque_id', $mosqueId)
                ->where('status', 'Berhasil')
                ->whereBetween('tanggal', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('nominal'),
            'relawanAktif' => KegiatanRelawan::whereHas('kegiatan', fn ($q) => $q->where('mosque_id', $mosqueId))
                ->distinct('nama')
                ->count('nama'),
        ];

        return view('pages.public.landing', [
            'activities' => $activities,
            'sholatJadwal' => $sholatJadwal,
            'stats' => $stats,
        ]);
    }
}
