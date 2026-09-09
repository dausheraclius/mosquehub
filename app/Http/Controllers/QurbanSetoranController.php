<?php

namespace App\Http\Controllers;

use App\Http\Resources\QurbanSetoranResource;
use App\Models\QurbanPeserta;
use App\Models\QurbanSetoran;
use App\Support\Concerns\HasMosqueContext;
use Illuminate\Http\Request;

class QurbanSetoranController extends Controller
{
    use HasMosqueContext;

    private const REQUIRED_STRING_255 = 'required|string|max:255';
    private const NULLABLE_STRING_100 = 'nullable|string|max:100';

    public function storeSetoran(Request $request)
    {
        $validated = $request->validate([
            'qurban_peserta_id' => 'required|integer',
            'qurban_peserta_member_id' => 'nullable|integer',
            'tanggal' => 'required|date',
            'jumlah' => 'required|numeric|min:1',
            'metode' => self::NULLABLE_STRING_100,
            'petugas' => self::REQUIRED_STRING_255,
        ]);

        $peserta = QurbanPeserta::forMosque()
            ->findOrFail($validated['qurban_peserta_id']);

        if (! empty($validated['qurban_peserta_member_id'])) {
            $peserta->members()->whereKey($validated['qurban_peserta_member_id'])->firstOrFail();
        }

        $setoran = QurbanSetoran::create([
            'qurban_peserta_id' => $peserta->id,
            'qurban_peserta_member_id' => $validated['qurban_peserta_member_id'] ?? null,
            'tanggal' => $validated['tanggal'],
            'jumlah' => $validated['jumlah'],
            'metode' => $validated['metode'] ?? null,
            'petugas' => $validated['petugas'],
        ]);

        return QurbanSetoranResource::make($setoran->load('member'));
    }

    public function updateSetoran(Request $request, $id)
    {
        $setoran = QurbanSetoran::whereHas(
            'peserta',
            fn ($q) => $q->forMosque(),
        )->findOrFail($id);

        $validated = $request->validate([
            'qurban_peserta_member_id' => 'nullable|integer',
            'tanggal' => 'required|date',
            'jumlah' => 'required|numeric|min:1',
            'metode' => self::NULLABLE_STRING_100,
            'petugas' => self::REQUIRED_STRING_255,
        ]);

        // Anggota harus milik peserta qurban dari setoran ini. Validasi `exists`
        // saja bisa menerima ID anggota dari peserta atau masjid lain.
        if (! empty($validated['qurban_peserta_member_id'])) {
            $setoran->peserta
                ->members()
                ->whereKey($validated['qurban_peserta_member_id'])
                ->firstOrFail();
        }

        $setoran->update([
            'qurban_peserta_member_id' => $validated['qurban_peserta_member_id'] ?? null,
            'tanggal' => $validated['tanggal'],
            'jumlah' => $validated['jumlah'],
            'metode' => $validated['metode'] ?? null,
            'petugas' => $validated['petugas'],
        ]);

        $setoran->refresh();

        return QurbanSetoranResource::make($setoran->load('member'));
    }

    public function destroySetoran($id)
    {
        $setoran = QurbanSetoran::whereHas('peserta', fn ($q) => $q->forMosque())
            ->findOrFail($id);
        $setoran->delete();

        return response()->json(['message' => 'Setoran dihapus']);
    }
}
