<?php

namespace App\Http\Controllers;

use App\Models\Donasi;
use App\Models\Jamaah;
use App\Models\KasTransaction;
use App\Models\Kegiatan;
use App\Models\KegiatanRelawan;

use App\Support\Concerns\HasMosqueContext;

class DashboardController extends Controller
{
    use HasMosqueContext;
    public function index()
    {
        $kas = KasTransaction::where('mosque_id', $this->mosqueId)->get();

        $saldoKas = (float) $kas->sum('pemasukan') - (float) $kas->sum('pengeluaran');

        $bulanIni = now()->startOfMonth();
        $donasiBulanIni = Donasi::where('mosque_id', $this->mosqueId)
            ->where('status', 'Berhasil')
            ->where('tanggal', '>=', $bulanIni)
            ->where('tanggal', '<=', now()->endOfMonth())
            ->sum('nominal');

        $relawanAktif = KegiatanRelawan::whereHas('kegiatan', fn ($q) => $q->where('mosque_id', $this->mosqueId))
            ->distinct('nama')
            ->count('nama');

        // Agregasi untuk grafik arus kas (6 bulan terakhir)
        $chart = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->startOfMonth()->subMonths($i);
            $chart[] = [
                'key' => $d->format('Y-m'),
                'label' => $d->translatedFormat('M'),
                'masuk' => (float) $kas->filter(fn ($t) => $t->tanggal?->format('Y-m') === $d->format('Y-m'))->sum('pemasukan'),
                'keluar' => (float) $kas->filter(fn ($t) => $t->tanggal?->format('Y-m') === $d->format('Y-m'))->sum('pengeluaran'),
            ];
        }

        // Donasi & Infaq berhasil per bulan (6 bulan terakhir)
        $donasiBerhasil = Donasi::where('mosque_id', $this->mosqueId)
            ->where('status', 'Berhasil')
            ->get();
        $donasiChart = [];
        $jamaahChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->startOfMonth()->subMonths($i);
            $key = $d->format('Y-m');
            $donasiChart[] = [
                'key' => $key,
                'label' => $d->translatedFormat('M'),
                'total' => (float) $donasiBerhasil->filter(fn ($x) => $x->tanggal?->format('Y-m') === $key)->sum('nominal'),
            ];
            $jamaahChart[] = [
                'key' => $key,
                'label' => $d->translatedFormat('M'),
                'baru' => Jamaah::where('mosque_id', $this->mosqueId)
                    ->whereYear('tanggal_bergabung', $d->year)
                    ->whereMonth('tanggal_bergabung', $d->month)
                    ->count(),
            ];
        }

        // Distribusi ziswaf berdasarkan kategori (6 terbesar)
        $ziswafDist = $donasiBerhasil
            ->groupBy(fn ($d2) => $d2->kategori ?: 'Lainnya')
            ->map(fn ($items) => round((float) $items->sum('nominal')))
            ->sortDesc()
            ->take(6)
            ->map(fn ($total, $label) => ['label' => $label, 'total' => $total])
            ->values()
            ->all();

        $agendaTerdekat = Kegiatan::where('mosque_id', $this->mosqueId)
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
        $infaqPerDonatur = Donasi::where('mosque_id', $this->mosqueId)
            ->where('status', 'Berhasil')
            ->get()
            ->groupBy(fn ($d) => mb_strtolower(trim((string) $d->donatur)))
            ->map(fn ($items) => (float) $items->sum('nominal'));

        $formatRupiah = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');

        $jamaahTerbaru = Jamaah::where('mosque_id', $this->mosqueId)
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
                'totalJemaah' => Jamaah::where('mosque_id', $this->mosqueId)->count(),
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