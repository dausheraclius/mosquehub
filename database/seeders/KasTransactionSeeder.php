<?php

namespace Database\Seeders;

use App\Models\KasTransaction;
use Illuminate\Database\Seeder;

class KasTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $mosqueId = 1;

        $jenisPemasukan = [
            'Kotak Infaq' => 'Operasional',
            'Infaq Jumat' => 'Operasional',
            'Donasi Pembangunan' => 'Pembangunan',
            'Zakat Fitrah' => 'Zakat',
            'Infaq Ramadhan' => 'Operasional',
            'Sumbangan Hari Besar' => 'Kegiatan',
        ];

        $jenisPengeluaran = [
            'Listrik dan Air' => 'Operasional',
            'Honor Ustaz' => 'Operasional',
            'Perawatan Masjid' => 'Perawatan',
            'Pembelian Peralatan' => 'Operasional',
            'Kegiatan Sosial' => 'Kegiatan',
            'Gaji Petugas' => 'Operasional',
        ];

        KasTransaction::where('mosque_id', $mosqueId)->delete();

        // 6 bulan terakhir (termasuk bulan berjalan)
        for ($i = 5; $i >= 0; $i--) {
            $bulan = now()->startOfMonth()->subMonths($i);

            $jumlahPemasukan = rand(3, 4);
            for ($j = 0; $j < $jumlahPemasukan; $j++) {
                $jenis = array_rand($jenisPemasukan);
                KasTransaction::create([
                    'mosque_id' => $mosqueId,
                    'tanggal' => $bulan->copy()->addDays(rand(1, $bulan->daysInMonth - 1))->toDateString(),
                    'jenis' => $jenis,
                    'kategori' => $jenisPemasukan[$jenis],
                    'keterangan' => null,
                    'pemasukan' => rand(50000, 2000000),
                    'pengeluaran' => 0,
                    'dibuat_oleh' => 'Ust. Daus Morgan',
                ]);
            }

            $jumlahPengeluaran = rand(2, 3);
            for ($j = 0; $j < $jumlahPengeluaran; $j++) {
                $jenis = array_rand($jenisPengeluaran);
                KasTransaction::create([
                    'mosque_id' => $mosqueId,
                    'tanggal' => $bulan->copy()->addDays(rand(1, $bulan->daysInMonth - 1))->toDateString(),
                    'jenis' => $jenis,
                    'kategori' => $jenisPengeluaran[$jenis],
                    'keterangan' => null,
                    'pemasukan' => 0,
                    'pengeluaran' => rand(50000, 1500000),
                    'dibuat_oleh' => 'Ust. Daus Morgan',
                ]);
            }
        }
    }
}
