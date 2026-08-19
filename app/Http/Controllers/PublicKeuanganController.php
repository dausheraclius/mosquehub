<?php

namespace App\Http\Controllers;

use App\Models\KasTransaction;
use App\Services\KeuanganStatistik;
use App\Support\SiteContext;

class PublicKeuanganController extends Controller
{
    public function index()
    {
        $mosqueId = SiteContext::mosqueId();
        $statistik = app(KeuanganStatistik::class);

        $ringkasan = $statistik->ringkasanKas($mosqueId);

        // Rekap kas 6 bulan terakhir
        $perBulan = $statistik->rekapKasBulanan($mosqueId);

        // Donasi & infaq berhasil per kategori
        $perKategori = $statistik->donasiPerKategori($mosqueId);
        $totalDonasi = (float) $perKategori->sum('total');

        // Transaksi kas terbaru
        $transaksiTerbaru = KasTransaction::forMosque($mosqueId)
            ->orderByDesc('tanggal')
            ->take(20)
            ->get()
            ->map(fn ($t) => [
                'tanggal' => $t->tanggal?->translatedFormat('d M Y') ?? '-',
                'keterangan' => $t->keterangan ?: $t->jenis,
                'tipe' => $t->tipe,
                'nominal' => (float) ($t->tipe === 'Pengeluaran' ? $t->pengeluaran : $t->pemasukan),
            ]);

        $formatRupiah = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');

        return view('pages.public.keuangan', [
            'saldo' => $ringkasan['saldo'],
            'pemasukan' => $ringkasan['pemasukan'],
            'pengeluaran' => $ringkasan['pengeluaran'],
            'totalDonasi' => $totalDonasi,
            'perBulan' => $perBulan,
            'perKategori' => $perKategori,
            'transaksiTerbaru' => $transaksiTerbaru,
            'formatRupiah' => $formatRupiah,
        ]);
    }
}
