import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
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
                'resources/assets/css/landing.css',
                'resources/assets/css/login.css',
            ],
            refresh: true,
        }),
    ],
});
