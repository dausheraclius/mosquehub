<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMenuAccess
{
    /**
     * Role yang otomatis punya akses penuh ke semua menu.
     */
    private const FULL_ACCESS_ROLES = ['Ketua YMBPK'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && in_array($user->role, self::FULL_ACCESS_ROLES, true)) {
            return $next($request);
        }

        $required = $this->requiredKeys($request->route()?->getName() ?? '');

        // Route yang tidak butuh izin khusus (dashboard, logout, csrf, publik)
        if ($required === []) {
            return $next($request);
        }

        $granted = (array) ($user?->permissions ?? []);

        if (count(array_intersect($required, $granted)) === 0) {
            abort(403, 'Anda tidak memiliki akses ke menu ini.');
        }

        return $next($request);
    }

    /**
     * Kunci menu yang dibutuhkan untuk sebuah route.
     *
     * Kunci mengikuti label yang dipakai di form "Hak Akses Menu"
     * (Manajemen Pengguna), plus kunci grup sebagai akses "payung"
     * (mis. punya grup "keuangan" berarti boleh akses semua submenunya).
     */
    private function requiredKeys(string $routeName): array
    {
        return match (true) {
            $routeName === 'dashboard' => [],
            str_starts_with($routeName, 'jamaah') => ['data-jamaah'],
            str_starts_with($routeName, 'keuangan.infaq') => ['Infaq / Sodaqoh (Zakat)', 'keuangan'],
            str_starts_with($routeName, 'keuangan.kas') => ['Kas Masjid', 'keuangan'],
            str_starts_with($routeName, 'kegiatan.agenda') => ['Agenda', 'kegiatan'],
            str_starts_with($routeName, 'kegiatan.jadwal') => ['Jadwal Kegiatan', 'kegiatan'],
            str_starts_with($routeName, 'kegiatan.petugas') => ['Jadwal Petugas Sholat', 'kegiatan'],
            str_starts_with($routeName, 'kegiatan.galeri') => ['Galeri', 'kegiatan'],
            str_starts_with($routeName, 'pengumuman') => ['pengumuman'],
            str_starts_with($routeName, 'kepengurusan') => ['kepengurusan'],
            str_starts_with($routeName, 'relawan') => ['relawan'],
            str_starts_with($routeName, 'surat') => ['surat'],
            str_starts_with($routeName, 'inventaris') => ['inventaris'],
            str_starts_with($routeName, 'laporan') => ['laporan'],
            $routeName === 'ekspor.pengumuman' => ['pengumuman'],
            $routeName === 'ekspor.surat' => ['surat'],
            $routeName === 'ekspor.inventaris' => ['inventaris'],
            $routeName === 'ekspor.agenda' || $routeName === 'ekspor.jadwal' => ['Agenda', 'Jadwal Kegiatan', 'kegiatan'],
            $routeName === 'ekspor.kepengurusan' => ['kepengurusan'],
            $routeName === 'ekspor.relawan' => ['relawan'],
            $routeName === 'ekspor.jadwal-petugas' => ['Jadwal Petugas Sholat', 'kegiatan'],
            $routeName === 'activity-log' => ['audit-log'],
            str_starts_with($routeName, 'pengaturan.profil') => ['Profile Masjid', 'pengaturan'],
            str_starts_with($routeName, 'pengaturan.umum') => ['Umum', 'pengaturan'],
            str_starts_with($routeName, 'pengaturan.user-management') => ['User Management', 'pengaturan'],
            default => [],
        };
    }
}
