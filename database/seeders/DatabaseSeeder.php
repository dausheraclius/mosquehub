<?php

namespace Database\Seeders;

use App\Models\Mosque;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Masjid utama (id 1) — wajib ada supaya operasi tulis
        // (inventaris, surat, dll.) tidak gagal karena mosque_id null.
        $mosque = Mosque::firstOrCreate(
            ['id' => 1],
            ['name' => 'Masjid Al-Firdaus', 'status' => 'aktif']
        );

        User::firstOrCreate(
            ['email' => 'admin@mosquehub.com'],
            [
                'name' => 'Daus',
                'password' => bcrypt('admin123'),
                'role' => 'Ketua YMBPK',
                'mosque_id' => $mosque->id,
            ]
        );
    }
}
