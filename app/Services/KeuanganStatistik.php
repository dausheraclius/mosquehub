<?php

namespace App\Services;

use App\Models\Donasi;
use App\Models\Jamaah;
use App\Models\KasTransaction;
use App\Models\KegiatanRelawan;
use Illuminate\Support\Collection;

/**
 * Agregasi keuangan & jamaah yang dipakai bersama dashboard admin,
 * halaman publik, dan laporan — supaya logika "6 bulan terakhir",
 * saldo kas, dan distribusi ziswaf tidak diulang di banyak controller.
 */
class KeuanganStatistik
{
    public function saldoKas(int $mosqueId): float
    {
        return (float) KasTransaction::forMosque($mosqueId)->sum('pemasukan')
            - (float) KasTransaction::forMosque($mosqueId)->sum('pengeluaran');
    }

    /** @return array{pemasukan: float, pengeluaran: float, saldo: float} */
    public function ringkasanKas(int $mosqueId): array
    {
        $pemasukan = (float) KasTransaction::forMosque($mosqueId)->sum('pemasukan');
        $pengeluaran = (float) KasTransaction::forMosque($mosqueId)->sum('pengeluaran');

        return [
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'saldo' => $pemasukan - $pengeluaran,
        ];
    }

    public function donasiBulanIni(int $mosqueId): float
    {
        return (float) Donasi::forMosque($mosqueId)
            ->where('status', 'Berhasil')
            ->whereBetween('tanggal', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('nominal');
    }

    public function totalDonasi(int $mosqueId): float
    {
        return (float) Donasi::forMosque($mosqueId)
            ->where('status', 'Berhasil')
            ->sum('nominal');
    }

    public function relawanAktif(int $mosqueId): int
    {
        return KegiatanRelawan::whereHas('kegiatan', fn ($q) => $q->forMosque($mosqueId))
            ->distinct('nama')
            ->count('nama');
    }

    /**
     * Arus kas per bulan (pemasukan/pengeluaran) untuk grafik.
     *
     * @return array<int, array{key: string, label: string, masuk: float, keluar: float}>
     */
    public function arusKasBulanan(int $mosqueId, int $bulan = 6): array
    {
        $chart = [];
        for ($i = $bulan - 1; $i >= 0; $i--) {
            $d = now()->startOfMonth()->subMonths($i);
            $chart[] = [
                'key' => $d->format('Y-m'),
                'label' => $d->translatedFormat('M'),
                'masuk' => (float) KasTransaction::forMosque($mosqueId)
                    ->whereMonth('tanggal', $d->month)
                    ->whereYear('tanggal', $d->year)
                    ->sum('pemasukan'),
                'keluar' => (float) KasTransaction::forMosque($mosqueId)
                    ->whereMonth('tanggal', $d->month)
                    ->whereYear('tanggal', $d->year)
                    ->sum('pengeluaran'),
            ];
        }

        return $chart;
    }

    /**
     * Total donasi/infaq berhasil per bulan untuk grafik.
     *
     * @return array<int, array{key: string, label: string, total: float}>
     */
    public function donasiBulanan(int $mosqueId, int $bulan = 6): array
    {
        $chart = [];
        for ($i = $bulan - 1; $i >= 0; $i--) {
            $d = now()->startOfMonth()->subMonths($i);
            $chart[] = [
                'key' => $d->format('Y-m'),
                'label' => $d->translatedFormat('M'),
                'total' => (float) Donasi::forMosque($mosqueId)
                    ->where('status', 'Berhasil')
                    ->whereMonth('tanggal', $d->month)
                    ->whereYear('tanggal', $d->year)
                    ->sum('nominal'),
            ];
        }

        return $chart;
    }

    /**
     * Jumlah jamaah baru per bulan untuk grafik.
     *
     * @return array<int, array{key: string, label: string, baru: int}>
     */
    public function jamaahBaruBulanan(int $mosqueId, int $bulan = 6): array
    {
        $chart = [];
        for ($i = $bulan - 1; $i >= 0; $i--) {
            $d = now()->startOfMonth()->subMonths($i);
            $chart[] = [
                'key' => $d->format('Y-m'),
                'label' => $d->translatedFormat('M'),
                'baru' => Jamaah::forMosque($mosqueId)
                    ->whereYear('tanggal_bergabung', $d->year)
                    ->whereMonth('tanggal_bergabung', $d->month)
                    ->count(),
            ];
        }

        return $chart;
    }

    /**
     * Distribusi ziswaf berdasarkan kategori (diurutkan menurun).
     *
     * @return array<int, array{label: string, total: float}>
     */
    public function distribusiZiswaf(int $mosqueId, int $limit = 6): array
    {
        return Donasi::forMosque($mosqueId)
            ->where('status', 'Berhasil')
            ->get()
            ->groupBy(fn ($d) => $d->kategori ?: 'Lainnya')
            ->map(fn ($items) => round((float) $items->sum('nominal')))
            ->sortDesc()
            ->take($limit)
            ->map(fn ($total, $label) => ['label' => $label, 'total' => $total])
            ->values()
            ->all();
    }

    /**
     * Rekap donasi/infaq per kategori (label, total, jumlah transaksi).
     */
    public function donasiPerKategori(int $mosqueId): Collection
    {
        return Donasi::forMosque($mosqueId)
            ->where('status', 'Berhasil')
            ->get()
            ->groupBy(fn ($d) => $d->kategori ?: 'Lainnya')
            ->map(fn ($items, $label) => [
                'label' => $label,
                'total' => (float) $items->sum('nominal'),
                'jumlah' => $items->count(),
            ])
            ->sortByDesc('total')
            ->values();
    }

    /**
     * Rekap kas per bulan untuk tabel ringkasan publik.
     *
     * @return array<int, array{label: string, masuk: float, keluar: float, saldo: float}>
     */
    public function rekapKasBulanan(int $mosqueId, int $bulan = 6): array
    {
        $perBulan = [];
        for ($i = $bulan - 1; $i >= 0; $i--) {
            $d = now()->startOfMonth()->subMonths($i);
            $masuk = (float) KasTransaction::forMosque($mosqueId)
                ->whereMonth('tanggal', $d->month)
                ->whereYear('tanggal', $d->year)
                ->sum('pemasukan');
            $keluar = (float) KasTransaction::forMosque($mosqueId)
                ->whereMonth('tanggal', $d->month)
                ->whereYear('tanggal', $d->year)
                ->sum('pengeluaran');
            $perBulan[] = [
                'label' => $d->translatedFormat('F Y'),
                'masuk' => $masuk,
                'keluar' => $keluar,
                'saldo' => $masuk - $keluar,
            ];
        }

        return $perBulan;
    }
}
