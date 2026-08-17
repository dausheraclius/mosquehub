<?php

namespace App\Console\Commands;

use App\Models\PengaturanUmum;
use App\Services\MosqueBackupService;
use Illuminate\Console\Command;

class CreateMosqueBackups extends Command
{
    protected $signature = 'mosquehub:backup';
    protected $description = 'Membuat backup otomatis untuk masjid yang mengaktifkan fitur backup.';

    public function handle(MosqueBackupService $backups): int
    {
        PengaturanUmum::where('backup_otomatis', true)->each(function (PengaturanUmum $setting) use ($backups): void {
            if (! $this->isDue($setting->backup_frekuensi)) {
                return;
            }

            $path = $backups->saveAutomatic($setting->mosque_id);
            $this->line('Backup dibuat: ' . $path);
        });

        return self::SUCCESS;
    }

    private function isDue(?string $frequency): bool
    {
        return match ($frequency) {
            'Setiap Minggu' => now()->isSunday(),
            'Setiap Bulan' => now()->isFirstOfMonth(),
            default => true,
        };
    }
}
