<?php

namespace App\Http\Controllers;

use App\Models\Donasi;
use App\Models\Jamaah;
use App\Models\QurbanPeserta;
use App\Models\QurbanPesertaMember;
use App\Models\QurbanSetoran;
use Illuminate\Http\Request;

use App\Support\Concerns\HasMosqueContext;

class InfaqSodaqohController extends Controller
{
    use HasMosqueContext;
    public function index()
    {
        $donasiList = Donasi::where('mosque_id', $this->mosqueId)
            ->orderByDesc('tanggal')
            ->get()
            ->map(fn ($d) => $this->donasiToArray($d));

        $qurbanList = QurbanPeserta::where('mosque_id', $this->mosqueId)
            ->with('setorans')
            ->get()
            ->map(fn ($p) => $this->pesertaToArray($p));

        $petugasList = Jamaah::where('status_jamaah', '!=', 'Wafat')
            ->whereNotNull('nama')
            ->orderBy('nama')
            ->pluck('nama')
            ->unique()
            ->values()
            ->all();

        $jamaahList = Jamaah::where('status_jamaah', '!=', 'Wafat')
            ->whereNotNull('nama')
            ->orderBy('nama')
            ->get(['id', 'nama'])
            ->map(fn ($j) => ['id' => $j->id, 'nama' => $j->nama])
            ->values()
            ->all();

        return view('pages.keuangan.infaq-sodaqoh', [
            'donasiList' => $donasiList,
            'qurbanList' => $qurbanList,
            'petugasList' => $petugasList,
            'jamaahList' => $jamaahList,
        ]);
    }

    public function storeDonasi(Request $request)
    {
        $validated = $request->validate($this->rules());

        $validated['mosque_id'] = $this->mosqueId;

        $donasi = Donasi::create($validated);

        return response()->json($this->donasiToArray($donasi));
    }

    public function updateDonasi(Request $request, $id)
    {
        $donasi = Donasi::where('mosque_id', $this->mosqueId)->findOrFail($id);

        $validated = $request->validate($this->rules());

        $donasi->update($validated);

        return response()->json($this->donasiToArray($donasi));
    }

    public function destroyDonasi($id)
    {
        $donasi = Donasi::where('mosque_id', $this->mosqueId)->findOrFail($id);
        $donasi->delete();

        return response()->json(['message' => 'Donasi dihapus']);
    }

    public function storePeserta(Request $request)
    {
        $validated = $request->validate($this->rulesPeserta());

        $validated['mosque_id'] = $this->mosqueId;
        $members = $request->input('members', []);

        if ($validated['paket'] === 'Patungan Sapi') {
            if (count($members) < 1 || count($members) > 7) {
                return response()->json(
                    ['errors' => ['members' => ['Anggota patungan minimal 1 dan maksimal 7 orang.']]],
                    422,
                );
            }
            $validated['nama'] = 'Patungan Sapi';
        }

        $peserta = QurbanPeserta::create($validated);

        if ($validated['paket'] === 'Patungan Sapi') {
            foreach ($members as $m) {
                $peserta->members()->create([
                    'jamaah_id' => $m['jamaah_id'] ?? null,
                    'nama' => $m['nama'],
                ]);
            }
        }

        return response()->json($this->pesertaToArray($peserta));
    }

    public function updatePeserta(Request $request, $id)
    {
        $peserta = QurbanPeserta::where('mosque_id', $this->mosqueId)->findOrFail($id);

        $validated = $request->validate($this->rulesPeserta());

        $members = $request->input('members', []);

        if ($validated['paket'] === 'Patungan Sapi') {
            if (count($members) < 1 || count($members) > 7) {
                return response()->json(
                    ['errors' => ['members' => ['Anggota patungan minimal 1 dan maksimal 7 orang.']]],
                    422,
                );
            }
            $validated['nama'] = 'Patungan Sapi';

            $peserta->members()->delete();
            foreach ($members as $m) {
                $peserta->members()->create([
                    'jamaah_id' => $m['jamaah_id'] ?? null,
                    'nama' => $m['nama'],
                ]);
            }
        }

        $peserta->update([
            'nama' => $validated['nama'],
            'paket' => $validated['paket'],
            'target' => $validated['target'],
            'mulai' => $validated['mulai'],
        ]);

        return response()->json($this->pesertaToArray($peserta->load('members', 'setorans')));
    }

    public function storeAnggota(Request $request, $id)
    {
        $peserta = QurbanPeserta::where('mosque_id', $this->mosqueId)->findOrFail($id);

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
            'jamaah_id' => 'nullable|exists:jamaah,id',
        ]);

        $peserta->members()->create($validated);

        return response()->json($this->pesertaToArray($peserta));
    }

    public function destroyAnggota($id)
    {
        $member = QurbanPesertaMember::whereHas(
            'peserta',
            fn ($q) => $q->where('mosque_id', $this->mosqueId),
        )->findOrFail($id);
        $member->delete();

        return response()->json(['message' => 'Anggota dihapus']);
    }

    public function storeSetoran(Request $request)
    {
        $validated = $request->validate([
            'qurban_peserta_id' => 'required|exists:qurban_pesertas,id',
            'qurban_peserta_member_id' => 'nullable|exists:qurban_peserta_members,id',
            'tanggal' => 'required|date',
            'jumlah' => 'required|numeric|min:1',
            'metode' => 'nullable|string|max:100',
            'petugas' => 'required|string|max:255',
        ]);

        $setoran = QurbanSetoran::create([
            'qurban_peserta_id' => $validated['qurban_peserta_id'],
            'qurban_peserta_member_id' => $validated['qurban_peserta_member_id'] ?? null,
            'tanggal' => $validated['tanggal'],
            'jumlah' => $validated['jumlah'],
            'metode' => $validated['metode'] ?? null,
            'petugas' => $validated['petugas'],
        ]);

        return response()->json([
            'id' => $setoran->id,
            'peserta_id' => $setoran->qurban_peserta_id,
            'member_id' => $setoran->qurban_peserta_member_id,
            'member_nama' => $setoran->member?->nama,
            'tanggal' => $setoran->tanggal->format('Y-m-d'),
            'jumlah' => (float) $setoran->jumlah,
            'metode' => $setoran->metode,
            'petugas' => $setoran->petugas,
        ]);
    }

    public function updateSetoran(Request $request, $id)
    {
        $setoran = QurbanSetoran::whereHas(
            'peserta',
            fn ($q) => $q->where('mosque_id', $this->mosqueId),
        )->findOrFail($id);

        $validated = $request->validate([
            'qurban_peserta_member_id' => 'nullable|exists:qurban_peserta_members,id',
            'tanggal' => 'required|date',
            'jumlah' => 'required|numeric|min:1',
            'metode' => 'nullable|string|max:100',
            'petugas' => 'required|string|max:255',
        ]);

        $setoran->update([
            'qurban_peserta_member_id' => $validated['qurban_peserta_member_id'] ?? null,
            'tanggal' => $validated['tanggal'],
            'jumlah' => $validated['jumlah'],
            'metode' => $validated['metode'] ?? null,
            'petugas' => $validated['petugas'],
        ]);

        $setoran->refresh();

        return response()->json([
            'id' => $setoran->id,
            'peserta_id' => $setoran->qurban_peserta_id,
            'member_id' => $setoran->qurban_peserta_member_id,
            'member_nama' => $setoran->member?->nama,
            'tanggal' => $setoran->tanggal->format('Y-m-d'),
            'jumlah' => (float) $setoran->jumlah,
            'metode' => $setoran->metode,
            'petugas' => $setoran->petugas,
        ]);
    }

    public function destroyPeserta($id)
    {
        $peserta = QurbanPeserta::where('mosque_id', $this->mosqueId)->findOrFail($id);
        $peserta->delete();

        return response()->json(['message' => 'Peserta dihapus']);
    }

    public function destroySetoran($id)
    {
        $setoran = QurbanSetoran::whereHas('peserta', fn ($q) => $q->where('mosque_id', $this->mosqueId))
            ->findOrFail($id);
        $setoran->delete();

        return response()->json(['message' => 'Setoran dihapus']);
    }

    private function rules(): array
    {
        return [
            'tanggal' => 'required|date',
            'tanggal_akhir' => 'nullable|date',
            'donatur' => 'required|string|max:255',
            'kategori' => "required|in:Zakat,Zakat Fitrah,Zakat Maal,Infaq,Infaq Jumat,Infaq Harian,Sodaqoh,Sodaqoh Dhuafa,Sodaqoh Anak Yatim,Sodaqoh Bencana,Wakaf,Wakaf Uang,Wakaf Tanah,Wakaf Bangunan,Wakaf Al-Qur'an,Donasi,Donasi Bencana,Donasi Pendidikan,Donasi Kesehatan,Donasi Umum",
            'jenis' => 'required|string|max:255',
            'tipe' => 'required|in:Uang,Barang',
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
            'metode' => 'nullable|string|max:100',
            'petugas' => 'required|string|max:255',
            'status' => 'required|in:Berhasil,Pending',
        ];
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
            'members.*.jamaah_id' => 'nullable|exists:jamaah,id',
        ];
    }

    private function donasiToArray(Donasi $d): array
    {
        return [
            'id' => $d->id,
            'tanggal' => $d->tanggal->format('d/m/Y'),
            'tanggal_akhir' => $d->tanggal_akhir ? $d->tanggal_akhir->format('d/m/Y') : null,
            'donatur' => $d->donatur,
            'kategori' => $d->kategori,
            'jenis' => $d->jenis,
            'tipe' => $d->tipe,
            'nominal' => (float) $d->nominal,
            'keterangan' => $d->keterangan ?: '-',
            'metode' => $d->metode ?: '-',
            'petugas' => $d->petugas,
            'status' => $d->status,
        ];
    }

    private function pesertaToArray(QurbanPeserta $p): array
    {
        return [
            'id' => $p->id,
            'nama' => $p->nama,
            'paket' => $p->paket,
            'is_patungan' => $p->paket === 'Patungan Sapi',
            'target' => (float) $p->target,
            'mulai' => $p->mulai->format('Y-m-d'),
            'members' => $p->members->map(fn ($m) => [
                'id' => $m->id,
                'jamaah_id' => $m->jamaah_id,
                'nama' => $m->nama,
            ])->values(),
            'riwayat' => $p->setorans->map(fn ($s) => [
                'id' => $s->id,
                'tanggal' => $s->tanggal->format('Y-m-d'),
                'jumlah' => (float) $s->jumlah,
                'metode' => $s->metode,
                'petugas' => $s->petugas,
                'member_id' => $s->qurban_peserta_member_id,
                'member_nama' => $s->member?->nama,
            ])->values(),
        ];
    }
}