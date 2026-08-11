<?php

namespace Database\Seeders;

use App\Models\Pengumuman;
use Illuminate\Database\Seeder;

class PengumumanSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'mosque_id' => 1,
                'judul' => 'Kajian Akbar Muharram',
                'isi' => 'Mari hadiri kajian akbar dalam rangka menyambut Tahun Baru Hijriah. Kajian akan diisi oleh Ust. Hakim dan dihadiri seluruh jamaah masjid.',
                'kategori' => 'Kegiatan',
                'status' => 'Aktif',
                'tanggal' => '2026-07-15',
            ],
            [
                'mosque_id' => 1,
                'judul' => 'Jadwal Pembagian Zakat Fitrah',
                'isi' => 'Pembagian zakat fitrah akan dilaksanakan di halaman masjid. Mohon jamaah membawa identitas dan mengikuti antrean yang sudah ditentukan.',
                'kategori' => 'Umum',
                'status' => 'Aktif',
                'tanggal' => '2026-07-01',
            ],
            [
                'mosque_id' => 1,
                'judul' => 'Rapat Pengurus YMBPK',
                'isi' => 'Rapat bulanan pengurus YMBPK membahas program kerja dan laporan keuangan. Rapat dimulai setelah shalat Isya.',
                'kategori' => 'Umum',
                'status' => 'Terjadwal',
                'tanggal' => '2026-08-20',
            ],
            [
                'mosque_id' => 1,
                'judul' => 'Gotong Royong Pembersihan Masjid',
                'isi' => 'Kegiatan jumsih bulanan untuk membersihkan area masjid dan halaman. Ditunggu partisipasi seluruh jamaah.',
                'kategori' => 'Kegiatan',
                'status' => 'Terjadwal',
                'tanggal' => '2026-08-25',
            ],
            [
                'mosque_id' => 1,
                'judul' => 'Pengumuman Libur Kegiatan Ramadhan Lalu',
                'isi' => 'Dokumentasi pengumuman kegiatan Ramadhan tahun lalu yang telah diarsipkan.',
                'kategori' => 'Umum',
                'status' => 'Arsip',
                'tanggal' => '2026-03-20',
            ],
        ];

        foreach ($items as $item) {
            Pengumuman::firstOrCreate(
                ['judul' => $item['judul'], 'mosque_id' => $item['mosque_id']],
                $item,
            );
        }
    }
}
