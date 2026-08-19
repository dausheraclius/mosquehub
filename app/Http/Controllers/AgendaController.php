<?php

namespace App\Http\Controllers;

use App\Models\Jamaah;
use App\Models\Kegiatan;
use Illuminate\Http\Request;

use App\Support\Concerns\HasMosqueContext;

class AgendaController extends Controller
{
    use HasMosqueContext;
    public function index()
    {
        $agendaList = Kegiatan::forMosque()
            ->orderBy('tanggal')
            ->get()
            ->map(fn ($k) => $this->toArray($k));

        $stats = [
            'hariIni' => Kegiatan::forMosque()
                ->whereDate('tanggal', now()->toDateString())
                ->count(),
            'mingguIni' => Kegiatan::forMosque()
                ->whereBetween('tanggal', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()])
                ->count(),
            'bulanIni' => Kegiatan::forMosque()
                ->whereBetween('tanggal', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->count(),
            'selesai' => Kegiatan::forMosque()
                ->where('status', 'Selesai')
                ->count(),
        ];

        $highlight = Kegiatan::forMosque()
            ->whereDate('tanggal', now()->toDateString())
            ->orderBy('jam_mulai')
            ->first();

        return view('pages.kegiatan.agenda', [
            'agendaList' => $agendaList,
            'highlight' => $highlight ? $this->toArray($highlight) : null,
            'stats' => $stats,
            'jamaahNames' => Jamaah::forMosque()
                ->orderBy('nama')
                ->pluck('nama'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['mosque_id'] = $this->mosqueId;

        $kegiatan = Kegiatan::create($validated);

        return response()->json($this->toArray($kegiatan));
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $kegiatan->update($this->validated($request));

        return response()->json($this->toArray($kegiatan));
    }

    public function destroy(Kegiatan $kegiatan)
    {
        $kegiatan->delete();

        return response()->json(['success' => true]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i',
            'lokasi' => 'nullable|string|max:255',
            'pemateri' => 'nullable|string|max:255',
            'pj' => 'nullable|string|max:255',
            'peserta' => 'nullable|integer',
            'status' => 'required|in:Akan Datang,Berlangsung,Selesai,Dibatalkan',
            'deskripsi' => 'nullable|string',
        ]);
    }

    private function toArray(Kegiatan $k): array
    {
        return [
            'id' => $k->id,
            'tanggal' => $k->tanggal->format('Y-m-d'),
            'nama' => $k->nama,
            'kategori' => $k->kategori,
            'jamMulai' => $k->jam_mulai ? substr($k->jam_mulai, 0, 5) : '',
            'jamSelesai' => $k->jam_selesai ? substr($k->jam_selesai, 0, 5) : '',
            'lokasi' => $k->lokasi,
            'pemateri' => $k->pemateri,
            'pj' => $k->pj,
            'peserta' => $k->peserta,
            'status' => $k->status,
            'statusClass' => match ($k->status) {
                'Berlangsung' => 'status-berlangsung',
                'Selesai' => 'status-selesai',
                'Akan Datang' => 'status-akan-datang',
                'Dibatalkan' => 'status-nonaktif',
                default => 'status-nonaktif',
            },
            'deskripsi' => $k->deskripsi,
        ];
    }
}