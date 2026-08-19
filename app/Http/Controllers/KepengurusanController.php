<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\Jamaah;
use Illuminate\Http\Request;

use App\Support\Concerns\HasMosqueContext;

class KepengurusanController extends Controller
{
    use HasMosqueContext;
    public function index()
    {
        $jabatans = Jabatan::where('mosque_id', $this->mosqueId)
            ->with('jamaah')
            ->orderBy('urutan')
            ->get();

        $namaList = $jabatans->pluck('nama')->values();

        $hierarki = [];
        $penempatan = [];
        $posisi = [];
        foreach ($jabatans as $j) {
            $parentNama = $j->parent_id ? optional($jabatans->firstWhere('id', $j->parent_id))->nama : null;
            $hierarki[$j->nama] = $parentNama;
            $penempatan[$j->nama] = $j->jamaah?->id ?: null;

            // Posisi visual (x/y) — terpisah dari hierarki. Null = belum diatur.
            if ($j->posisi_x !== null && $j->posisi_y !== null) {
                $posisi[$j->nama] = ['x' => (int) $j->posisi_x, 'y' => (int) $j->posisi_y];
            }
        }

        // Pool jemaah yang bisa dipilih jadi pengurus (dicocokin pakai ID biar anti dobel nama/email)
        $daftarJamaah = Jamaah::where('mosque_id', $this->mosqueId)
            ->orderBy('nama')
            ->get()
            ->map(fn ($j) => [
                'id' => $j->id,
                'nama' => $j->nama,
                'email' => $j->email,
                'hp' => $j->no_hp,
            ]);

        return view('pages.kepengurusan', [
            'namaJabatanList' => $namaList,
            'hierarki' => $hierarki,
            'penempatan' => $penempatan,
            'posisiOrg' => $posisi,
            'daftarJamaah' => $daftarJamaah,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'parent_nama' => 'nullable|string|max:255',
        ]);

        if (Jabatan::where('mosque_id', $this->mosqueId)->where('nama', $validated['nama'])->exists()) {
            return response()->json(['message' => "Jabatan \"{$validated['nama']}\" udah ada."], 422);
        }

        $parent = $validated['parent_nama']
            ? Jabatan::where('mosque_id', $this->mosqueId)->where('nama', $validated['parent_nama'])->first()
            : null;

        $maxUrutan = Jabatan::where('mosque_id', $this->mosqueId)->max('urutan') ?? 0;

        Jabatan::create([
            'mosque_id' => $this->mosqueId,
            'nama' => $validated['nama'],
            'parent_id' => $parent?->id,
            'urutan' => $maxUrutan + 1,
        ]);

        return response()->json(['success' => true]);
    }

    public function updateParent(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string',
            'parent_nama' => 'nullable|string',
        ]);

        $jabatan = Jabatan::where('mosque_id', $this->mosqueId)->where('nama', $validated['nama'])->firstOrFail();
        $parent = $validated['parent_nama']
            ? Jabatan::where('mosque_id', $this->mosqueId)->where('nama', $validated['parent_nama'])->first()
            : null;

        $jabatan->update(['parent_id' => $parent?->id]);

        return response()->json(['success' => true]);
    }

    public function updatePositions(Request $request)
    {
        $validated = $request->validate([
            'positions' => 'required|array',
            'positions.*.x' => 'required|integer|min:0|max:100000',
            'positions.*.y' => 'required|integer|min:0|max:100000',
        ]);

        $jabatans = Jabatan::where('mosque_id', $this->mosqueId)->get()->keyBy('nama');

        foreach ($validated['positions'] as $nama => $pos) {
            $jabatan = $jabatans->get($nama);
            if (!$jabatan) continue;

            $jabatan->update([
                'posisi_x' => $pos['x'],
                'posisi_y' => $pos['y'],
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function updatePenempatan(Request $request)
    {
        $penempatan = $request->input('penempatan', []); // { "Nama Jabatan": jamaahId | null }

        foreach ($penempatan as $namaJabatan => $jamaahId) {
            $jabatan = Jabatan::where('mosque_id', $this->mosqueId)->where('nama', $namaJabatan)->first();
            if (!$jabatan) continue;

            $jamaah = $jamaahId ? Jamaah::where('mosque_id', $this->mosqueId)->where('id', $jamaahId)->first() : null;
            $jabatan->update(['jamaah_id' => $jamaah?->id]);
        }

        return response()->json(['success' => true]);
    }

    public function renameJabatan(Request $request)
    {
    $validated = $request->validate([
        'nama_lama' => 'required|string',
        'nama_baru' => 'required|string|max:255',
    ]);

    $jabatan = Jabatan::where('mosque_id', $this->mosqueId)->where('nama', $validated['nama_lama'])->firstOrFail();

    if ($validated['nama_baru'] !== $validated['nama_lama'] &&
        Jabatan::where('mosque_id', $this->mosqueId)->where('nama', $validated['nama_baru'])->exists()) {
        return response()->json(['message' => 'Nama jabatan itu udah dipakai.'], 422);
    }

    $jabatan->update(['nama' => $validated['nama_baru']]);

    return response()->json(['success' => true]);
}

public function destroyJabatan(Request $request)
{
    $validated = $request->validate(['nama' => 'required|string']);

    Jabatan::where('mosque_id', $this->mosqueId)->where('nama', $validated['nama'])->delete();
    // anak-anaknya otomatis pindah ke level teratas (nullOnDelete di migration)

    return response()->json(['success' => true]);
}

public function resetJabatan()
{
    Jabatan::where('mosque_id', $this->mosqueId)->delete();

    $defaults = ['Ketua YMBPK', 'Wakil Ketua YMBPK', 'Sekretaris YMBPK', 'Bendahara YMBPK', 'Sie Pendidikan', 'Sie Pembangunan'];
    $parentId = null;
    foreach ($defaults as $i => $nama) {
        $j = Jabatan::create([
            'mosque_id' => $this->mosqueId,
            'nama' => $nama,
            'parent_id' => $i === 0 ? null : $parentId,
            'urutan' => $i,
        ]);
        if ($i === 0) $parentId = $j->id;
    }

    return response()->json(['success' => true]);
}
}
