<?php

namespace App\Http\Resources\Concerns;

use Illuminate\Support\CarbonInterface;

trait FormatTanggalIndonesia
{
    protected function hariIndonesia(string $day): string
    {
        return [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ][$day] ?? $day;
    }

    protected function tanggalLabel(CarbonInterface $date): string
    {
        $bulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];

        return $date->format('d').' '.$bulan[$date->format('m')].' '.$date->format('Y');
    }

    protected function formatTanggalLahir(?CarbonInterface $date): string
    {
        if (! $date) {
            return '-';
        }

        $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        return $date->format('d').' '.$bulan[$date->month - 1].' '.$date->format('Y');
    }

    protected function formatTanggalBergabung(?CarbonInterface $date): string
    {
        if (! $date) {
            return '-';
        }

        $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];

        return $date->format('d').' '.$bulan[$date->month - 1].' '.$date->format('Y');
    }
}
