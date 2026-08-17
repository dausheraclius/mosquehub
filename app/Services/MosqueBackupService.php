<?php

namespace App\Services;

use App\Models\Donasi;
use App\Models\Inventaris;
use App\Models\Jabatan;
use App\Models\JadwalPetugasSholat;
use App\Models\Jamaah;
use App\Models\KasTransaction;
use App\Models\Kegiatan;
use App\Models\KegiatanRelawan;
use App\Models\Pengumuman;
use App\Models\QurbanPeserta;
use App\Models\QurbanPesertaMember;
use App\Models\QurbanSetoran;
use App\Models\Surat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MosqueBackupService
{
    /** @var array<string, class-string<\Illuminate\Database\Eloquent\Model>> */
    private const MODELS = [
        'jamaah' => Jamaah::class,
        'donasi' => Donasi::class,
        'kas_transaksi' => KasTransaction::class,
        'kegiatan' => Kegiatan::class,
        'kegiatan_relawan' => KegiatanRelawan::class,
        'pengumuman' => Pengumuman::class,
        'inventaris' => Inventaris::class,
        'surat' => Surat::class,
        'jabatan' => Jabatan::class,
        'jadwal_petugas_sholat' => JadwalPetugasSholat::class,
        'qurban_peserta' => QurbanPeserta::class,
        'qurban_peserta_member' => QurbanPesertaMember::class,
        'qurban_setoran' => QurbanSetoran::class,
    ];

    public function snapshot(int $mosqueId): array
    {
        $data = [
            'format' => 'mosquehub-backup-v1',
            'diambil_pada' => now()->toIso8601String(),
            'masjid_id' => $mosqueId,
        ];

        foreach (self::MODELS as $key => $model) {
            $data[$key] = $this->forMosque($key, $model, $mosqueId)->get()->toArray();
        }

        return $data;
    }

    public function saveAutomatic(int $mosqueId): string
    {
        $path = 'backups/masjid-' . $mosqueId . '/backup-' . now()->format('Y-m-d-His') . '.json';
        Storage::disk('local')->put($path, json_encode($this->snapshot($mosqueId), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $path;
    }

    public function restore(string $contents, int $mosqueId): void
    {
        $backup = json_decode($contents, true);

        if (! is_array($backup) || ($backup['format'] ?? null) !== 'mosquehub-backup-v1') {
            throw ValidationException::withMessages([
                'file' => 'File bukan backup MosqueHub yang valid.',
            ]);
        }

        DB::transaction(function () use ($backup, $mosqueId): void {
            // Hapus anak terlebih dahulu supaya constraint relasi tetap aman.
            foreach (array_reverse(self::MODELS, true) as $key => $model) {
                $this->forMosque($key, $model, $mosqueId)->delete();
            }

            foreach (self::MODELS as $key => $model) {
                $rows = $backup[$key] ?? [];
                if (! is_array($rows) || $rows === []) {
                    continue;
                }

                $table = (new $model)->getTable();
                $columns = (new $model)->getConnection()->getSchemaBuilder()->getColumnListing($table);
                $prepared = collect($rows)
                    ->filter(fn ($row) => is_array($row))
                    ->map(function (array $row) use ($columns, $mosqueId) {
                        $row = array_intersect_key($row, array_flip($columns));
                        if (in_array('mosque_id', $columns, true)) {
                            $row['mosque_id'] = $mosqueId;
                        }

                        return $row;
                    })
                    ->values()
                    ->all();

                if ($prepared !== []) {
                    DB::table($table)->insert($prepared);
                }
            }
        });
    }

    private function forMosque(string $key, string $model, int $mosqueId)
    {
        return match ($key) {
            'kegiatan_relawan' => $model::whereHas('kegiatan', fn ($query) => $query->where('mosque_id', $mosqueId)),
            'qurban_peserta_member' => $model::whereHas('peserta', fn ($query) => $query->where('mosque_id', $mosqueId)),
            'qurban_setoran' => $model::whereHas('peserta', fn ($query) => $query->where('mosque_id', $mosqueId)),
            default => $model::where('mosque_id', $mosqueId),
        };
    }
}
