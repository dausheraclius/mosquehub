<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\PengaturanUmum;
use App\Services\MosqueBackupService;
use App\Support\Concerns\HasMosqueContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UmumController extends Controller
{
    use HasMosqueContext;

    public function index()
    {
        $pengaturan = PengaturanUmum::firstOrCreate(['mosque_id' => $this->mosqueId]);

        $namaJabatanPerOrganisasi = Jabatan::forMosque()
            ->whereIn('organisasi', ['YMBPK', 'IKRAM'])
            ->orderBy('urutan')
            ->get()
            ->groupBy('organisasi')
            ->map(fn ($jabatans) => $jabatans->pluck('nama'));

        return view('pages.pengaturan.umum', [
            'pengaturan' => $pengaturan,
            'namaJabatanPerOrganisasi' => $namaJabatanPerOrganisasi,
        ]);
    }

    public function updateProfilAplikasi(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'timezone' => 'required|string|max:50',
            'date_format' => 'required|string|max:50',
        ]);

        PengaturanUmum::forMosque()->update($validated);

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

        PengaturanUmum::forMosque()->update($validated);

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
    public function exportData(MosqueBackupService $backups)
    {
        $fileName = 'backup-masjid-'.now()->format('Y-m-d-Hi').'.json';
        $snapshot = $backups->snapshot($this->mosqueId);

        return response()->streamDownload(function () use ($snapshot) {
            echo json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $fileName, ['Content-Type' => 'application/json']);
    }

    /**
     * Pulihkan backup JSON MosqueHub secara atomik.
     */
    public function importData(Request $request, MosqueBackupService $backups)
    {
        $request->validate([
            'file' => 'required|file|mimes:json,txt|max:20480',
        ]);

        $backups->restore(file_get_contents($request->file('file')->getRealPath()), $this->mosqueId);

        return response()->json([
            'success' => true,
            'message' => 'Backup berhasil dipulihkan.',
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

        if (! Hash::check($validated['old_password'], $user->password)) {
            return response()->json(['message' => 'Password lama salah.'], 422);
        }

        $user->update(['password' => bcrypt($validated['new_password'])]);

        return response()->json(['success' => true]);
    }
}
