<?php

namespace App\Http\Controllers;

use App\Models\KasTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Support\Concerns\HasMosqueContext;

class KasMasjidController extends Controller
{
    use HasMosqueContext;

    private const REQUIRED_STRING_255 = 'required|string|max:255';
    private const NULLABLE_STRING_255 = 'nullable|string|max:255';
    public function index()
    {
        $mosqueId = $this->mosqueId;

        $all = KasTransaction::forMosque($mosqueId)->orderByDesc('tanggal')->get();

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
        $validated = $request->validate($this->rules());

        $transaksi = KasTransaction::create([
            'mosque_id' => $this->mosqueId,
            'tanggal' => $validated['tanggal'],
            'jenis' => $validated['jenis'],
            'kategori' => $validated['kategori'] ?? null,
            'keterangan' => $validated['keterangan'] ?? null,
            'pemasukan' => $validated['tipe'] === 'Pemasukan' ? $validated['jumlah'] : 0,
            'pengeluaran' => $validated['tipe'] === 'Pengeluaran' ? $validated['jumlah'] : 0,
            'dibuat_oleh' => auth()->user()->name ?? 'Ketua YMBPK',
        ]);

        return response()->json($this->toArrayForFrontend($transaksi));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate($this->rules());

        $transaksi = KasTransaction::forMosque()->findOrFail($id);

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
        $transaksi = KasTransaction::forMosque()->findOrFail($id);
        $transaksi->delete();

        return response()->json(['success' => true]);
    }

    private function rules(): array
    {
        return [
            'tanggal' => 'required|date',
            'jenis' => self::REQUIRED_STRING_255,
            'kategori' => self::NULLABLE_STRING_255,
            'keterangan' => self::NULLABLE_STRING_255,
            'tipe' => 'required|in:Pemasukan,Pengeluaran',
            'jumlah' => 'required|numeric|min:0',
        ];
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