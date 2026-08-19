<?php

namespace App\Http\Controllers;

use App\Models\Donasi;
use App\Models\Jamaah;
use App\Models\Kegiatan;
use App\Services\KeuanganStatistik;
use App\Support\Concerns\HasMosqueContext;

class DashboardController extends Controller
{
    use HasMosqueContext;

    public function index()
    {
        $statistik = app(KeuanganStatistik::class);
        $saldoKas = $statistik->saldoKas($this->mosqueId);
        $donasiBulanIni = $statistik->donasiBulanIni($this->mosqueId);
        $relawanAktif = $statistik->relawanAktif($this->mosqueId);

        // Agregasi untuk grafik arus kas (6 bulan terakhir)
        $chart = $statistik->arusKasBulanan($this->mosqueId);

        // Donasi & Infaq berhasil per bulan (6 bulan terakhir)
        $donasiChart = $statistik->donasiBulanan($this->mosqueId);
        $jamaahChart = $statistik->jamaahBaruBulanan($this->mosqueId);

        // Distribusi ziswaf berdasarkan kategori (6 terbesar)
        $ziswafDist = $statistik->distribusiZiswaf($this->mosqueId);

        $agendaTerdekat = Kegiatan::forMosque()
            ->where('tanggal', '>=', now()->toDateString())
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->take(3)
            ->get()
            ->map(fn ($k) => [
                'day' => $k->tanggal->format('d'),
                'month' => $k->tanggal->translatedFormat('M'),
                'title' => $k->nama,
                'sub' => $k->pemateri ?: ($k->pj ?: ($k->jam_mulai ? substr($k->jam_mulai, 0, 5) : '-')),
            ]);

        // Total donasi berhasil per donatur (dipakai buat kolom "Riwayat Infaq")
        $infaqPerDonatur = Donasi::forMosque()
            ->where('status', 'Berhasil')
            ->get()
            ->groupBy(fn ($d) => mb_strtolower(trim((string) $d->donatur)))
            ->map(fn ($items) => (float) $items->sum('nominal'));

        $formatRupiah = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');

        $jamaahTerbaru = Jamaah::forMosque()
            ->orderByDesc('created_at')
            ->take(5)
            ->get()
            ->map(function ($j) use ($infaqPerDonatur, $formatRupiah) {
                $key = mb_strtolower(trim($j->nama));
                $total = $infaqPerDonatur->get($key);

                // Fallback: cocokkan lewat email kalau donatur berupa email
                if ($total === null && $j->email) {
                    $total = $infaqPerDonatur->get(mb_strtolower(trim($j->email)));
                }

                return [
                    'nama' => $j->nama,
                    'status' => $j->status_jamaah ?: 'Aktif',
                    'tanggalBergabung' => $j->tanggal_bergabung
                        ? $j->tanggal_bergabung->translatedFormat('d M Y')
                        : '-',
                    'tanggalBergabungIso' => $j->tanggal_bergabung ? $j->tanggal_bergabung->format('Y-m-d') : '',
                    'infaq' => $total !== null ? $formatRupiah($total) : 'Rp 0',
                ];
            });

        return view('pages.dashboard', [
            'stats' => [
                'totalJemaah' => Jamaah::forMosque()->count(),
                'saldoKas' => $saldoKas,
                'infaqBulanIni' => (float) $donasiBulanIni,
                'relawanAktif' => $relawanAktif,
            ],
            'chart' => $chart,
            'donasiChart' => $donasiChart,
            'jamaahChart' => $jamaahChart,
            'ziswafDist' => $ziswafDist,
            'agendaTerdekat' => $agendaTerdekat,
            'jamaahTerbaru' => $jamaahTerbaru,
        ]);
    }
}
