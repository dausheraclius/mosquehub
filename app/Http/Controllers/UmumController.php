<?php

namespace App\Http\Controllers;

use App\Models\Donasi;
use App\Models\Inventaris;
use App\Models\Jabatan;
use App\Models\Jamaah;
use App\Models\KasTransaction;
use App\Models\Kegiatan;
use App\Models\PengaturanUmum;
use App\Models\Pengumuman;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Support\Concerns\HasMosqueContext;

class UmumController extends Controller
{
    use HasMosqueContext;
    public function index()
    {
        $pengaturan = PengaturanUmum::firstOrCreate(['mosque_id' => $this->mosqueId]);

        $namaJabatanList = Jabatan::where('mosque_id', $this->mosqueId)->orderBy('urutan')->pluck('nama');

        return view('pages.pengaturan.umum', [
            'pengaturan' => $pengaturan,
            'namaJabatanList' => $namaJabatanList,
        ]);
    }

    public function updateProfilAplikasi(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'timezone' => 'required|string|max:50',
            'date_format' => 'required|string|max:50',
        ]);

        PengaturanUmum::where('mosque_id', $this->mosqueId)->update($validated);

        return response()->json(['success' => true]);
    }

    public function updateNotifikasi(Request $request)
    {
        $validated = $request->validate([
            'wa_gateway_number' => 'nullable|string|max:50',
            'notif_infaq_bulanan' => 'boolean',
            'notif_agenda_kegiatan' => 'boolean',
            'notif_jamaah_baru' => 'boolean',
            'notif_laporan_mingguan' => 'boolean',
        ]);

        PengaturanUmum::where('mosque_id', $this->mosqueId)->update($validated);

        return response()->json(['success' => true]);
    }

    public function updateBackup(Request $request)
    {
        $validated = $request->validate([
            'backup_otomatis' => 'boolean',
            'backup_frekuensi' => 'required|string|max:50',
        ]);

        $pengaturan = PengaturanUmum::firstOrCreate(['mosque_id' => $this->mosqueId]);
        $pengaturan->update($validated);

        return response()->json(['success' => true]);
    }

    /**
     * Backup sekarang / ekspor data — unduh snapshot JSON semua data masjid.
     */
    public function exportData()
    {
        $m = $this->mosqueId;

        $snapshot = [
            'diambil_pada' => now()->toDateTimeString(),
            'masjid_id' => $m,
            'jamaah' => Jamaah::where('mosque_id', $m)->get()->toArray(),
            'donasi' => Donasi::where('mosque_id', $m)->get()->toArray(),
            'kas_transaksi' => KasTransaction::where('mosque_id', $m)->get()->toArray(),
            'kegiatan' => Kegiatan::where('mosque_id', $m)->get()->toArray(),
            'pengumuman' => Pengumuman::where('mosque_id', $m)->get()->toArray(),
            'inventaris' => Inventaris::where('mosque_id', $m)->get()->toArray(),
            'surat' => Surat::where('mosque_id', $m)->get()->toArray(),
            'jabatan' => Jabatan::where('mosque_id', $m)->get()->toArray(),
        ];

        $fileName = 'backup-masjid-' . now()->format('Y-m-d-Hi') . '.json';

        return response()->streamDownload(function () use ($snapshot) {
            echo json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $fileName, ['Content-Type' => 'application/json']);
    }

    /**
     * Impor data — terima file CSV berisi data jamaah, buat baris baru.
     * Kolom yang dikenali: nama, jenis_kelamin, no_hp, email, alamat,
     * status_jamaah, tanggal_bergabung.
     */
    public function importData(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

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
                    continue; // ada baris header, lewati
                }
                $header = null; // bukan header, perlakukan sebagai data
            }

            $data = $header
                ? array_combine($header, array_pad($row, count($header), null))
                : ['nama' => $row[0] ?? null, 'jenis_kelamin' => $row[1] ?? null, 'no_hp' => $row[2] ?? null, 'email' => $row[3] ?? null, 'alamat' => $row[4] ?? null, 'status_jamaah' => $row[5] ?? null, 'tanggal_bergabung' => $row[6] ?? null];

            $nama = $data['nama'] ?? null;
            if (!$nama) {
                continue;
            }

            if (Jamaah::where('mosque_id', $this->mosqueId)->where('nama', $nama)->exists()) {
                $errors[] = "\"$nama\" sudah ada, dilewati";
                continue;
            }

            Jamaah::create([
                'mosque_id' => $this->mosqueId,
                'nama' => $nama,
                'jenis_kelamin' => in_array($data['jenis_kelamin'] ?? null, ['Laki-laki', 'Perempuan'], true) ? $data['jenis_kelamin'] : 'Laki-laki',
                'no_hp' => $data['no_hp'] ?? null,
                'email' => $data['email'] ?? null,
                'alamat' => $data['alamat'] ?? null,
                'status_jamaah' => in_array($data['status_jamaah'] ?? null, ['Aktif', 'Tidak Aktif', 'Pindah', 'Wafat'], true) ? $data['status_jamaah'] : 'Aktif',
                'tanggal_bergabung' => $data['tanggal_bergabung'] ?? now()->toDateString(),
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

    public function updateJadwalSholat(Request $request)
    {
        $validated = $request->validate([
            'sholat_subuh' => 'required|date_format:H:i',
            'sholat_dzuhur' => 'required|date_format:H:i',
            'sholat_ashar' => 'required|date_format:H:i',
            'sholat_maghrib' => 'required|date_format:H:i',
            'sholat_isya' => 'required|date_format:H:i',
            'tampil_sholat_subuh' => 'boolean',
            'tampil_sholat_dzuhur' => 'boolean',
            'tampil_sholat_ashar' => 'boolean',
            'tampil_sholat_maghrib' => 'boolean',
            'tampil_sholat_isya' => 'boolean',
        ]);

        $pengaturan = PengaturanUmum::firstOrCreate(['mosque_id' => $this->mosqueId]);
        $pengaturan->update($validated);

        return response()->json(['success' => true]);
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($validated['old_password'], $user->password)) {
            return response()->json(['message' => 'Password lama salah.'], 422);
        }

        $user->update(['password' => bcrypt($validated['new_password'])]);

        return response()->json(['success' => true]);
    }
}