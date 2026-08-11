<?php

namespace App\Http\Controllers;

use App\Models\KasTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class KasMasjidController extends Controller
{
    public function index()
    {
        $mosqueId = 1; // TODO: ganti ke mosque_id user login setelah Auth dibikin

        $all = KasTransaction::where('mosque_id', $mosqueId)->orderByDesc('tanggal')->get();

        $saldoSekarang = $all->sum('pemasukan') - $all->sum('pengeluaran');

        $bulanIni = $all->filter(fn ($t) => $t->tanggal->isSameMonth(now()) && $t->tanggal->isSameYear(now()));
        $tahunIni = $all->filter(fn ($t) => $t->tanggal->isSameYear(now()));

        $stats = [
            'saldoSekarang' => $saldoSekarang,
            'pemasukanBulanIni' => $bulanIni->sum('pemasukan'),
            'pengeluaranBulanIni' => $bulanIni->sum('pengeluaran'),
            'pemasukanTahunIni' => $tahunIni->sum('pemasukan'),
            'pengeluaranTahunIni' => $tahunIni->sum('pengeluaran'),
            'jumlahTransaksi' => $all->count(),
        ];

        $dataTransaksi = $all->map(fn ($t) => $this->toArrayForFrontend($t));

        $dataAktivitas = $all->take(4)->map(function ($t) {
            return [
                'title' => $t->keterangan ?: $t->jenis,
                'time' => $t->created_at->format('H:i'),
                'tipe' => $t->pemasukan > 0 ? 'plus' : 'minus',
            ];
        });

        $dataBulan = $all
            ->groupBy(fn ($t) => $t->tanggal->format('Y-m'))
            ->map(fn ($items, $key) => [
                'value' => $key,
                'label' => Carbon::createFromFormat('Y-m-d', $key . '-01')->translatedFormat('F Y'),
            ])
            ->values()
            ->sortByDesc('value')
            ->values()
            ->all();

        $dataKategori = $all->pluck('kategori')->filter()->unique()->values()->all();

        return view('pages.keuangan.kas-masjid', [
            'stats' => $stats,
            'dataTransaksi' => $dataTransaksi,
            'dataAktivitas' => $dataAktivitas,
            'dataBulan' => $dataBulan,
            'dataKategori' => $dataKategori,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jenis' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:255',
            'tipe' => 'required|in:Pemasukan,Pengeluaran',
            'jumlah' => 'required|numeric|min:0',
        ]);

        $transaksi = KasTransaction::create([
            'mosque_id' => 1, // TODO: ganti setelah Auth dibikin
            'tanggal' => $validated['tanggal'],
            'jenis' => $validated['jenis'],
            'kategori' => $validated['kategori'] ?? null,
            'keterangan' => $validated['keterangan'] ?? null,
            'pemasukan' => $validated['tipe'] === 'Pemasukan' ? $validated['jumlah'] : 0,
            'pengeluaran' => $validated['tipe'] === 'Pengeluaran' ? $validated['jumlah'] : 0,
            'dibuat_oleh' => 'Ust. Daus Morgan', // TODO: ganti ke nama user login
        ]);

        return response()->json($this->toArrayForFrontend($transaksi));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jenis' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:255',
            'tipe' => 'required|in:Pemasukan,Pengeluaran',
            'jumlah' => 'required|numeric|min:0',
        ]);

        $transaksi = KasTransaction::findOrFail($id);

        $transaksi->update([
            'tanggal' => $validated['tanggal'],
            'jenis' => $validated['jenis'],
            'kategori' => $validated['kategori'] ?? null,
            'keterangan' => $validated['keterangan'] ?? null,
            'pemasukan' => $validated['tipe'] === 'Pemasukan' ? $validated['jumlah'] : 0,
            'pengeluaran' => $validated['tipe'] === 'Pengeluaran' ? $validated['jumlah'] : 0,
        ]);

        return response()->json($this->toArrayForFrontend($transaksi));
    }

    public function destroy($id)
    {
        $transaksi = KasTransaction::findOrFail($id);
        $transaksi->delete();

        return response()->json(['success' => true]);
    }

    private function toArrayForFrontend(KasTransaction $t): array
    {
        return [
            'id' => $t->id,
            'tanggal' => $t->tanggal->format('d/m/y'),
            'tanggalIso' => $t->tanggal->format('Y-m-d'),
            'jenis' => $t->jenis,
            'kategori' => $t->kategori,
            'ket' => $t->keterangan,
            'masuk' => (float) $t->pemasukan,
            'keluar' => (float) $t->pengeluaran,
            'tipe' => (float) $t->pemasukan > 0 ? 'Pemasukan' : 'Pengeluaran',
            'oleh' => $t->dibuat_oleh,
        ];
    }
}