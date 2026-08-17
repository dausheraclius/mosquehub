<?php

namespace App\Http\Controllers;

use App\Models\Jamaah;
use App\Support\Concerns\HasMosqueContext;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class JamaahController extends Controller
{
    use HasMosqueContext;

    public function index()
    {
        $mosqueId = $this->mosqueId;
        $jamaahList = Jamaah::where('mosque_id', $mosqueId)->orderBy('nama')->get()->map(function ($j) {
            return $this->toArrayForFrontend($j);
        });

        $stats = [
            'total' => $jamaahList->count(),
            'laki' => $jamaahList->filter(fn ($a) => str_starts_with($a['gender'], 'Laki-laki'))->count(),
            'perempuan' => $jamaahList->filter(fn ($a) => str_starts_with($a['gender'], 'Perempuan'))->count(),
            'aktif' => $jamaahList->filter(fn ($a) => $a['status'] === 'Aktif')->count(),
        ];

        return view('pages.jamaah.index', [
            'jamaahList' => $jamaahList,
            'stats' => $stats,
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $mosqueId = $this->mosqueId;
        $handle = fopen($request->file('file')->getRealPath(), 'r');
        if ($handle === false) {
            return response()->json(['message' => 'Gagal membaca file.'], 422);
        }

        $header = null;
        $imported = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $row = array_map('trim', $row);
            if ($header === null) {
                $header = array_map('strtolower', $row);
                if (in_array('nama', $header, true)) {
                    continue;
                }
                $header = null;
            }

            $data = $header
                ? array_combine($header, array_slice(array_pad($row, count($header), null), 0, count($header)))
                : ['nama' => $row[0] ?? null, 'jenis_kelamin' => $row[1] ?? null, 'no_hp' => $row[2] ?? null, 'email' => $row[3] ?? null, 'alamat' => $row[4] ?? null, 'status_jamaah' => $row[5] ?? null, 'tanggal_bergabung' => $row[6] ?? null];

            $nama = $data['nama'] ?? null;
            if (!$nama) {
                continue;
            }

            try {
                $tanggalBergabung = empty($data['tanggal_bergabung'])
                    ? now()->toDateString()
                    : Carbon::parse($data['tanggal_bergabung'])->toDateString();
            } catch (\Throwable) {
                $errors[] = "\"$nama\" memiliki tanggal bergabung tidak valid, dilewati";
                continue;
            }

            if (Jamaah::where('mosque_id', $mosqueId)->where('nama', $nama)->exists()) {
                $errors[] = "\"$nama\" sudah ada, dilewati";
                continue;
            }

            Jamaah::create([
                'mosque_id' => $mosqueId,
                'nama' => $nama,
                'jenis_kelamin' => in_array($data['jenis_kelamin'] ?? null, ['Laki-laki', 'Perempuan'], true) ? $data['jenis_kelamin'] : 'Laki-laki',
                'no_hp' => $data['no_hp'] ?? null,
                'email' => $data['email'] ?? null,
                'alamat' => $data['alamat'] ?? null,
                'status_jamaah' => in_array($data['status_jamaah'] ?? null, ['Aktif', 'Tidak Aktif', 'Pindah', 'Wafat'], true) ? $data['status_jamaah'] : 'Aktif',
                'tanggal_bergabung' => $tanggalBergabung,
            ]);
            $imported++;
        }

        fclose($handle);

        return response()->json([
            'success' => true,
            'imported' => $imported,
            'skipped' => $errors,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        $validated['mosque_id'] = $this->mosqueId;

        $jamaah = Jamaah::create($validated);

        $this->storeFoto($request, $jamaah);

        return response()->json($this->toArrayForFrontend($jamaah));
    }

    public function update(Request $request, $id)
    {

        $mosqueId = $this->mosqueId;
        $jamaah = Jamaah::where('mosque_id', $mosqueId)->findOrFail($id);

        $validated = $this->validateRequest($request);

        $jamaah->update($validated);

        $this->storeFoto($request, $jamaah);

        return response()->json($this->toArrayForFrontend($jamaah));
    }

    public function destroy($id)
    {

        $mosqueId = $this->mosqueId;
        $jamaah = Jamaah::where('mosque_id', $mosqueId)->findOrFail($id);

        if ($jamaah->foto) {
            Storage::disk('public')->delete($jamaah->foto);
        }

        $jamaah->delete();

        return response()->json(['message' => 'Jemaah dihapus']);
    }

    private function validateRequest(Request $request): array
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'no_hp' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'alamat' => 'nullable|string',
            'pekerjaan' => 'nullable|string|max:255',
            'status_pernikahan' => 'nullable|in:Belum Menikah,Menikah,Janda,Duda',
            'status_jamaah' => 'nullable|in:Aktif,Tidak Aktif,Pindah,Wafat',
            'tanggal_bergabung' => 'nullable|date',
            'catatan' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Foto profil ditangani terpisah via storeFoto(), jangan masuk mass-assignment
        unset($validated['foto']);

        return $validated;
    }

    // Simpan foto profil ke disk public, hapus foto lama kalau ada.
    private function storeFoto(Request $request, Jamaah $jamaah): void
    {
        if (!$request->hasFile('foto')) return;

        $oldFoto = $jamaah->getRawOriginal('foto');
        if ($oldFoto) {
            Storage::disk('public')->delete($oldFoto);
        }

        $path = $request->file('foto')->store('foto-jamaah', 'public');
        $jamaah->update(['foto' => $path]);
    }

    // Ubah 1 baris Jamaah jadi bentuk array persis kayak format dummy data lama,
    // biar data-jamaah.js gak perlu diubah logic render/filter-nya sama sekali.
    private function toArrayForFrontend(Jamaah $j): array
    {
        return [
            'id' => $j->id,
            'nama' => $j->nama,
            'gender' => $j->jenis_kelamin === 'Laki-laki' ? 'Laki-laki / Ikhwan' : 'Perempuan / Akhwat',
            'hp' => $j->no_hp,
            'email' => $j->email,
            'status' => $j->status_jamaah,
            'tempatLahir' => $j->tempat_lahir,
            'tanggalLahir' => $this->formatTanggalLahir($j->tanggal_lahir),
            'tanggalLahirIso' => $j->tanggal_lahir?->format('Y-m-d'),
            'alamat' => $j->alamat,
            'pekerjaan' => $j->pekerjaan,
            'statusPernikahan' => $j->status_pernikahan,
            'tanggalBergabung' => $this->formatTanggalBergabung($j->tanggal_bergabung),
            'tanggalBergabungIso' => $j->tanggal_bergabung?->format('Y-m-d'),
            'foto' => $j->foto ? '/storage/' . $j->foto : null,
            'catatan' => $j->catatan ?: '-',
        ];
    }

    private function formatTanggalLahir($date): string
    {
        if (!$date) return '-';
        $bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        return $date->format('d') . ' ' . $bulan[$date->month - 1] . ' ' . $date->format('Y');
    }

    private function formatTanggalBergabung($date): string
    {
        if (!$date) return '-';
        $bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        return $date->format('d') . ' ' . $bulan[$date->month - 1] . ' ' . $date->format('Y');
    }
}
