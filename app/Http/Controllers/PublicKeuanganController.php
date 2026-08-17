<?php

namespace App\Http\Controllers;

use App\Models\Donasi;
use App\Models\KasTransaction;
use App\Support\SiteContext;

class PublicKeuanganController extends Controller
{
    public function index()
    {
        $mosqueId = SiteContext::mosqueId();

        $kas = KasTransaction::where('mosque_id', $mosqueId)
            ->orderByDesc('tanggal')
            ->get();

        $pemasukan = (float) $kas->sum('pemasukan');
        $pengeluaran = (float) $kas->sum('pengeluaran');
        $saldo = $pemasukan - $pengeluaran;

        // Rekap kas 6 bulan terakhir
        $perBulan = collect();
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->startOfMonth()->subMonths($i);
            $key = $d->format('Y-m');
            $masuk = (float) $kas->filter(fn ($t) => $t->tanggal?->format('Y-m') === $key)->sum('pemasukan');
            $keluar = (float) $kas->filter(fn ($t) => $t->tanggal?->format('Y-m') === $key)->sum('pengeluaran');
            $perBulan[] = [
                'label' => $d->translatedFormat('F Y'),
                'masuk' => $masuk,
                'keluar' => $keluar,
                'saldo' => $masuk - $keluar,
            ];
        }

        // Donasi & infaq berhasil per kategori
        $donasi = Donasi::where('mosque_id', $mosqueId)
            ->where('status', 'Berhasil')
            ->get();
        $totalDonasi = (float) $donasi->sum('nominal');
        $perKategori = $donasi
            ->groupBy(fn ($d) => $d->kategori ?: 'Lainnya')
            ->map(fn ($items, $label) => [
                'label' => $label,
                'total' => (float) $items->sum('nominal'),
                'jumlah' => $items->count(),
            ])
            ->sortByDesc('total')
            ->values();

        // Transaksi kas terbaru
        $transaksiTerbaru = $kas->take(20)->map(fn ($t) => [
            'tanggal' => $t->tanggal?->translatedFormat('d M Y') ?? '-',
            'keterangan' => $t->keterangan ?: $t->jenis,
            'tipe' => $t->tipe,
            'nominal' => (float) ($t->tipe === 'Pengeluaran' ? $t->pengeluaran : $t->pemasukan),
        ]);

        $formatRupiah = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');

        return view('pages.public.keuangan', [
            'saldo' => $saldo,
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'totalDonasi' => $totalDonasi,
            'perBulan' => $perBulan,
            'perKategori' => $perKategori,
            'transaksiTerbaru' => $transaksiTerbaru,
            'formatRupiah' => $formatRupiah,
        ]);
    }
}
