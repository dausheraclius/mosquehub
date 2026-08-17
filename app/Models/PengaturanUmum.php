<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanUmum extends Model
{
    protected $fillable = [
        'mosque_id', 'app_name', 'timezone', 'date_format', 'wa_gateway_number',
        'notif_infaq_bulanan', 'notif_agenda_kegiatan', 'notif_jamaah_baru', 'notif_laporan_mingguan',
        'sholat_subuh', 'sholat_dzuhur', 'sholat_ashar', 'sholat_maghrib', 'sholat_isya',
        'tampil_sholat_subuh', 'tampil_sholat_dzuhur', 'tampil_sholat_ashar',
        'tampil_sholat_maghrib', 'tampil_sholat_isya',
        'backup_otomatis', 'backup_frekuensi',
    ];

    protected $casts = [
        'notif_infaq_bulanan' => 'boolean',
        'notif_agenda_kegiatan' => 'boolean',
        'notif_jamaah_baru' => 'boolean',
        'notif_laporan_mingguan' => 'boolean',
        'tampil_sholat_subuh' => 'boolean',
        'tampil_sholat_dzuhur' => 'boolean',
        'tampil_sholat_ashar' => 'boolean',
        'tampil_sholat_maghrib' => 'boolean',
        'tampil_sholat_isya' => 'boolean',
        'backup_otomatis' => 'boolean',
    ];

    public function setBackupFrekuensiAttribute($value)
    {
        $this->attributes['backup_frekuensi'] = $value ?: 'Setiap Hari';
    }

    /**
     * Jadwal sholat harian dalam urutan tampilan, lengkap dengan
     * nilai default kalau belum diatur. Dipakai di panel Pengaturan
     * maupun website publik (landing page).
     */
    public function sholatJadwal(): array
    {
        $definisi = [
            'subuh'   => ['label' => 'Subuh',   'waktu' => '04:30'],
            'dzuhur'  => ['label' => 'Dzuhur',  'waktu' => '12:00'],
            'ashar'   => ['label' => 'Ashar',   'waktu' => '15:15'],
            'maghrib' => ['label' => 'Maghrib', 'waktu' => '18:00'],
            'isya'    => ['label' => 'Isya',    'waktu' => '19:15'],
        ];

        $jadwal = [];
        foreach ($definisi as $key => $def) {
            // Kolom `time` MySQL menyimpan HH:MM:SS — potong ke HH:MM
            $waktu = (string) ($this->{'sholat_' . $key} ?? '');
            if (strlen($waktu) >= 5) {
                $waktu = substr($waktu, 0, 5);
            }

            $jadwal[$key] = [
                'label' => $def['label'],
                'waktu' => $waktu ?: $def['waktu'],
                // null (belum diatur) dianggap tampil, sesuai default kolom DB
                'tampil' => (bool) ($this->{'tampil_sholat_' . $key} ?? true),
            ];
        }

        return $jadwal;
    }
}
