<?php

namespace App\Http\Controllers;

use App\Http\Resources\QurbanPesertaResource;
use App\Models\Jamaah;
use App\Models\QurbanPeserta;
use App\Models\QurbanPesertaMember;
use App\Support\Concerns\HasMosqueContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QurbanPesertaController extends Controller
{
    use HasMosqueContext;

    public function storePeserta(Request $request)
    {
        $validated = $request->validate($this->rulesPeserta());

        $validated['mosque_id'] = $this->mosqueId;
        $members = $request->input('members', []);

        if ($validated['paket'] === 'Patungan Sapi') {
            $this->validatePatunganCount($members);
            $validated['nama'] = 'Patungan Sapi';
        }

        $peserta = DB::transaction(function () use ($validated, $members) {
            $peserta = QurbanPeserta::create($validated);

            if ($validated['paket'] === 'Patungan Sapi') {
                $this->attachMembers($peserta, $members);
            }

            return $peserta;
        });

        return QurbanPesertaResource::make($peserta->load('members', 'setorans'));
    }

    public function updatePeserta(Request $request, $id)
    {
        $peserta = QurbanPeserta::forMosque()->findOrFail($id);

        $validated = $request->validate($this->rulesPeserta());

        $members = $request->input('members', []);

        DB::transaction(function () use ($peserta, $validated, $members) {
            if ($validated['paket'] === 'Patungan Sapi') {
                $this->validatePatunganCount($members);
                $validated['nama'] = 'Patungan Sapi';
                $peserta->members()->delete();
                $this->attachMembers($peserta, $members);
            }

            $peserta->update([
                'nama' => $validated['nama'],
                'paket' => $validated['paket'],
                'target' => $validated['target'],
                'mulai' => $validated['mulai'],
            ]);
        });

        return QurbanPesertaResource::make($peserta->load('members', 'setorans'));
    }

    public function storeAnggota(Request $request, $id)
    {
        $peserta = QurbanPeserta::forMosque()->findOrFail($id);

        if ($peserta->paket !== 'Patungan Sapi') {
            return response()->json(
                ['errors' => ['anggota' => ['Paket ini tidak memakai anggota patungan.']]],
                422,
            );
        }

        if ($peserta->members()->count() >= 7) {
            return response()->json(
                ['errors' => ['anggota' => ['Anggota patungan sudah penuh (7/7).']]],
                422,
            );
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jamaah_id' => 'nullable|integer|exists:jamaah,id',
        ]);

        if ($validated['jamaah_id'] ?? null) {
            Jamaah::forMosque()->findOrFail($validated['jamaah_id']);
        }

        $peserta->members()->create($validated);

        return QurbanPesertaResource::make($peserta->load('members', 'setorans'));
    }

    public function destroyAnggota($id)
    {
        $member = QurbanPesertaMember::whereHas(
            'peserta',
            fn ($q) => $q->forMosque(),
        )->findOrFail($id);
        $member->delete();

        return response()->json(['message' => 'Anggota dihapus']);
    }

    public function destroyPeserta($id)
    {
        $peserta = QurbanPeserta::forMosque()->findOrFail($id);
        $peserta->delete();

        return response()->json(['message' => 'Peserta dihapus']);
    }

    private function rulesPeserta(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'paket' => 'required|in:Patungan Sapi,Kambing,Sapi Utuh',
            'target' => 'required|numeric|min:1',
            'mulai' => 'required|date',
            'members' => 'nullable|array|min:1|max:7',
            'members.*.nama' => 'required|string|max:255',
            'members.*.jamaah_id' => 'nullable|integer',
        ];
    }

    private function validatePatunganCount(array $members): void
    {
        if (count($members) < 1 || count($members) > 7) {
            throw ValidationException::withMessages([
                'members' => ['Anggota patungan minimal 1 dan maksimal 7 orang.'],
            ]);
        }
    }

    private function attachMembers(QurbanPeserta $peserta, array $members): void
    {
        foreach ($members as $m) {
            $jamaahId = $m['jamaah_id'] ?? null;
            if ($jamaahId) {
                Jamaah::forMosque()->findOrFail($jamaahId);
            }
            $peserta->members()->create([
                'jamaah_id' => $jamaahId,
                'nama' => $m['nama'],
            ]);
        }
    }
}
