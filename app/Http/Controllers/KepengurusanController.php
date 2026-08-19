<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\Jamaah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Support\Concerns\HasMosqueContext;

class KepengurusanController extends Controller
{
    use HasMosqueContext;

    private const ORGANISASI = ['YMBPK', 'IKRAM'];

    public function index(Request $request)
    {
        $organisasi = $this->setOrganisasi($request);
        $organisasiTersedia = self::ORGANISASI;
        $jabatans = Jabatan::forMosque()->forOrganisasi($organisasi)
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
        $daftarJamaah = Jamaah::forMosque()
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
            'organisasi' => $organisasi,
            'organisasiTersedia' => $organisasiTersedia,
        ]);
    }

    public function store(Request $request)
    {
        $organisasi = $this->setOrganisasi($request);
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'parent_nama' => 'nullable|string|max:255',
        ]);

        if (Jabatan::forMosque()->forOrganisasi($organisasi)->where('nama', $validated['nama'])->exists()) {
            return response()->json(['message' => "Jabatan \"{$validated['nama']}\" udah ada."], 422);
        }

        $parent = $this->parentFor($organisasi, $validated['parent_nama'] ?? null);

        $maxUrutan = Jabatan::forMosque()->forOrganisasi($organisasi)->max('urutan') ?? 0;

        Jabatan::create([
            'mosque_id' => $this->mosqueId,
            'organisasi' => $organisasi,
            'nama' => $validated['nama'],
            'parent_id' => $parent?->id,
            'urutan' => $maxUrutan + 1,
        ]);

        return response()->json(['success' => true]);
    }

    public function updateParent(Request $request)
    {
        $organisasi = $this->setOrganisasi($request);
        $validated = $request->validate([
            'nama' => 'required|string',
            'parent_nama' => 'nullable|string',
        ]);

        $jabatan = Jabatan::forMosque()->forOrganisasi($organisasi)->where('nama', $validated['nama'])->firstOrFail();
        $parent = $this->parentFor($organisasi, $validated['parent_nama'] ?? null);

        if ($parent?->is($jabatan)) {
            return response()->json(['message' => 'Jabatan tidak dapat menjadi atasan dirinya sendiri.'], 422);
        }

        if ($parent && $this->isDescendantOf($parent, $jabatan)) {
            return response()->json(['message' => 'Struktur jabatan tidak boleh membentuk siklus.'], 422);
        }

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

        $organisasi = $this->setOrganisasi($request);
        $jabatans = Jabatan::forMosque()->forOrganisasi($organisasi)->get()->keyBy('nama');

        $updated = 0;
        DB::transaction(function () use ($jabatans, $validated, &$updated): void {
            foreach ($validated['positions'] as $nama => $pos) {
                $jabatan = $jabatans->get($nama);
                if (!$jabatan) continue;

                $jabatan->update(['posisi_x' => $pos['x'], 'posisi_y' => $pos['y']]);
                $updated++;
            }
        });

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'total' => count($validated['positions']),
        ]);
    }

    public function updatePenempatan(Request $request)
    {
        $organisasi = $this->setOrganisasi($request);
        $validated = $request->validate([
            'penempatan' => 'required|array',
            'penempatan.*' => 'nullable|integer',
        ]);
        $penempatan = $validated['penempatan']; // { "Nama Jabatan": jamaahId | null }
        $assigned = collect($penempatan)->filter()->map(fn ($id) => (int) $id)->values();
        if ($assigned->count() !== $assigned->unique()->count()) {
            return response()->json(['message' => 'Satu jemaah hanya boleh ditempatkan pada satu jabatan.'], 422);
        }

        $jabatans = Jabatan::forMosque()->forOrganisasi($organisasi)->get()->keyBy('nama');
        $jamaahIds = $assigned->all();
        $jamaahTersedia = Jamaah::forMosque()->whereIn('id', $jamaahIds)->pluck('id')->map(fn ($id) => (int) $id);
        if ($jamaahTersedia->count() !== count($jamaahIds)) {
            return response()->json(['message' => 'Salah satu jemaah yang dipilih tidak tersedia.'], 422);
        }



        DB::transaction(function () use ($jabatans, $penempatan): void {
            foreach ($penempatan as $namaJabatan => $jamaahId) {
                $jabatan = $jabatans->get($namaJabatan);
                if (!$jabatan) {
                    continue;
                }

                $jabatan->update(['jamaah_id' => $jamaahId ?: null]);
            }
        });

        return response()->json(['success' => true]);
    }

    public function renameJabatan(Request $request)
    {
    $organisasi = $this->setOrganisasi($request);
    $validated = $request->validate([
        'nama_lama' => 'required|string',
        'nama_baru' => 'required|string|max:255',
    ]);

    $jabatan = Jabatan::forMosque()->forOrganisasi($organisasi)->where('nama', $validated['nama_lama'])->firstOrFail();

    if ($validated['nama_baru'] !== $validated['nama_lama'] &&
        Jabatan::forMosque()->forOrganisasi($organisasi)->where('nama', $validated['nama_baru'])->exists()) {
        return response()->json(['message' => 'Nama jabatan itu udah dipakai.'], 422);
    }

    $jabatan->update(['nama' => $validated['nama_baru']]);

    return response()->json(['success' => true]);
}

public function destroyJabatan(Request $request)
{
    $organisasi = $this->setOrganisasi($request);
    $validated = $request->validate(['nama' => 'required|string']);

    Jabatan::forMosque()->forOrganisasi($organisasi)->where('nama', $validated['nama'])->delete();
    // anak-anaknya otomatis pindah ke level teratas (nullOnDelete di migration)

    return response()->json(['success' => true]);
}

    public function resetJabatan(Request $request)
    {
    $organisasi = $this->setOrganisasi($request);

    $defaultsMap = [
        'YMBPK' => ['Ketua YMBPK', 'Wakil Ketua YMBPK', 'Sekretaris YMBPK', 'Bendahara YMBPK', 'Sie Pendidikan', 'Sie Pembangunan'],
        'IKRAM' => ['Ketua IKRAM', 'Wakil Ketua IKRAM', 'Sekretaris IKRAM', 'Bendahara IKRAM', 'Sie Kaderisasi', 'Sie Sosial'],
    ];
    $defaults = $defaultsMap[$organisasi] ?? $defaultsMap['YMBPK'];

    DB::transaction(function () use ($defaults, $organisasi): void {
        Jabatan::forMosque()->where('organisasi', $organisasi)->delete();
        $parentId = null;
        foreach ($defaults as $i => $nama) {
            $j = Jabatan::create(['mosque_id' => $this->mosqueId, 'organisasi' => $organisasi, 'nama' => $nama, 'parent_id' => $i === 0 ? null : $parentId, 'urutan' => $i]);
            if ($i === 0) $parentId = $j->id;
        }
    });

    return response()->json(['success' => true]);
}

    private function setOrganisasi(Request $request): string
    {
        $input = $request->input('organisasi') ?? session('kepengurusan_organisasi');
        $organisasi = strtoupper((string) ($input ?? 'YMBPK'));
        abort_unless(in_array($organisasi, self::ORGANISASI, true), 404);
        session(['kepengurusan_organisasi' => $organisasi]);
        return $organisasi;
    }

    private function parentFor(string $organisasi, ?string $nama): ?Jabatan
    {
        if (!$nama) {
            return null;
        }

        return Jabatan::forMosque()->forOrganisasi($organisasi)->where('nama', $nama)->firstOr(function () {
            abort(response()->json(['message' => 'Jabatan atasan tidak ditemukan.'], 422));
        });
    }

    private function isDescendantOf(Jabatan $candidate, Jabatan $ancestor): bool
    {
        $current = $candidate;
        $seen = [];

        while ($current->parent_id && !isset($seen[$current->id])) {
            if ($current->parent_id === $ancestor->id) {
                return true;
            }

            $seen[$current->id] = true;
            $current = $current->parent()->first();
            if (!$current) {
                break;
            }
        }

        return false;
    }
}
