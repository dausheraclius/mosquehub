<?php

namespace App\Http\Controllers;

use App\Models\JadwalPetugasSholat;
use Illuminate\Http\Request;

use App\Support\Concerns\HasMosqueContext;

class JadwalPetugasController extends Controller
{
    use HasMosqueContext;
    public function index()
    {
        $list = JadwalPetugasSholat::forMosque()
            ->orderBy('tanggal')
            ->orderByRaw("FIELD(sholat, 'Jumat', 'Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya')")
            ->get()
            ->map(fn ($j) => $this->toArray($j));

        return view('pages.kegiatan.jadwal-petugas', [
            'jadwalList' => $list,
            'jamaahOptions' => \App\Models\Jamaah::forMosque()
                ->orderBy('nama')
                ->get()
                ->map(fn ($j) => ['nama' => $j->nama, 'email' => $j->email ?? '', 'hp' => $j->no_hp ?? '']),
            'stats' => [
                'total' => $list->count(),
                'jumat' => JadwalPetugasSholat::forMosque()
                    ->where('sholat', 'Jumat')
                    ->count(),
                'bulanIni' => JadwalPetugasSholat::forMosque()
                    ->whereBetween('tanggal', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                    ->count(),
                'denganKhatib' => JadwalPetugasSholat::forMosque()
                    ->whereNotNull('khatib')
                    ->where('khatib', '!=', '')
                    ->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['mosque_id'] = $this->mosqueId;

        $jadwal = JadwalPetugasSholat::create($validated);

        return response()->json($this->toArray($jadwal));
    }

    public function update(Request $request, int $id)
    {
        $jadwal = JadwalPetugasSholat::forMosque()->findOrFail($id);

        $jadwal->update($this->validated($request));

        return response()->json($this->toArray($jadwal));
    }

    public function destroy(int $id)
    {
        $jadwal = JadwalPetugasSholat::forMosque()->findOrFail($id);

        $jadwal->delete();

        return response()->json(['success' => true]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'tanggal' => 'required|date',
            'sholat' => 'required|in:Jumat,Subuh,Dzuhur,Ashar,Maghrib,Isya',
            'khatib' => 'nullable|string|max:255',
            'imam' => 'nullable|string|max:255',
            'muadzin' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);
    }

    private function toArray(JadwalPetugasSholat $j): array
    {
        return [
            'id' => $j->id,
            'tanggal' => $j->tanggal->format('Y-m-d'),
            'hari' => $this->hariIndonesia($j->tanggal->format('l')),
            'sholat' => $j->sholat,
            'khatib' => $j->khatib,
            'imam' => $j->imam,
            'muadzin' => $j->muadzin,
            'keterangan' => $j->keterangan,
            'isJumat' => $j->sholat === 'Jumat',
            'lewat' => $j->tanggal->lt(now()->startOfDay()),
        ];
    }

    private function hariIndonesia(string $day): string
    {
        return [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ][$day] ?? $day;
    }
}