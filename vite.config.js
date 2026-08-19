import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        // Dev server ikut dibuka dari luar (sama seperti `artisan serve --host=0.0.0.0`)
        // biar perangkat lain bisa memuat CSS/JS saat melihat progress.
        host: '0.0.0.0',
        // Alamat yang ditulis ke public/hot → dipakai browser untuk memuat asset.
        // Harus alamat yang bisa dijangkau perangkat lain (ganti kalau IP berubah).
        hmr: { host: '192.168.20.224' },
    },
    plugins: [
        laravel({
            input: [
                'resources/assets/css/variables.css',
                'resources/assets/css/reset.css',
                'resources/assets/css/style.css',
                'resources/assets/css/topbar.css',
                'resources/assets/css/header.css',
                'resources/assets/css/sidebar.css',
                'resources/assets/css/content-header.css',
                'resources/assets/css/components.css',
                'resources/assets/css/custom-select.css',
                'resources/assets/css/responsive.css',
                'resources/assets/css/dashboard.css',
                'resources/assets/css/data-jamaah.css',
                'resources/assets/css/agenda.css',
                'resources/assets/css/jadwal-kegiatan.css',
                'resources/assets/css/jadwal-petugas.css',
                'resources/assets/css/galeri.css',
                'resources/assets/css/infaq-sodaqoh.css',
                'resources/assets/css/kas-masjid.css',
                'resources/assets/css/pengumuman.css',
                'resources/assets/css/kepengurusan.css',
                'resources/assets/css/relawan.css',
                'resources/assets/css/surat.css',
                'resources/assets/css/inventaris.css',
                'resources/assets/css/laporan.css',
                'resources/assets/css/profil-masjid.css',
                'resources/assets/css/umum.css',
                'resources/assets/css/user-management.css',
                'resources/assets/css/activity-log.css',
                'resources/assets/css/landing.css',
                'resources/assets/css/login.css',
            ],
            refresh: true,
        }),
    ],
});
