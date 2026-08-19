<?php

namespace App\Http\Controllers;

use App\Models\Jamaah;
use App\Models\Kegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Support\Concerns\HasMosqueContext;

class RelawanController extends Controller
{
    use HasMosqueContext;
    public function index()
    {
        $kegiatanList = Kegiatan::forMosque()
            ->with('relawans')
            ->orderByDesc('tanggal')
            ->get()
            ->map(fn ($k) => $this->toArray($k));

        $daftarJamaahRelawan = Jamaah::forMosque()
            ->orderBy('nama')
            ->get()
            ->map(fn ($j) => ['nama' => $j->nama, 'telepon' => $j->no_hp ?: '-']);

        return view('pages.relawan', [
            'kegiatanList' => $kegiatanList,
            'daftarJamaahRelawan' => $daftarJamaahRelawan,
        ]);
    }

    public function updateRelawan(Request $request, Kegiatan $kegiatan)
    {
        $validated = $request->validate([
            'relawan' => 'required|array',
            'relawan.*.nama' => 'required|string|max:255',
            'relawan.*.telepon' => 'nullable|string|max:50',
        ]);

        DB::transaction(function () use ($kegiatan, $validated): void {
            $kegiatan->relawans()->delete();
            foreach ($validated['relawan'] as $r) {
                $kegiatan->relawans()->create(['nama' => $r['nama'], 'telepon' => $r['telepon'] ?? '-']);
            }
        });

        return response()->json($this->toArray($kegiatan->fresh('relawans')));
    }

    private function toArray(Kegiatan $k): array
    {
        return [
            'id' => $k->id,
            'nama' => $k->nama,
            'icon' => 'fa-users',
            'tanggal' => $k->tanggal->translatedFormat('d M Y'),
            'lokasi' => $k->lokasi ?: '-',
            'slotMax' => $k->slot_relawan_max ?? 20,
            'status' => $k->status === 'Akan Datang' ? 'Akan Datang' : $k->status,
            'relawan' => $k->relawans->map(fn ($r) => ['nama' => $r->nama, 'telepon' => $r->telepon])->values(),
        ];
    }
}
