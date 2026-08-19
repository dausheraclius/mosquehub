<?php

namespace App\Support;

use App\Models\Mosque;

class SiteContext
{
    /**
     * ID masjid yang sedang aktif.
     *
     * - Sudah login  → ikut mosque_id user.
     * - Halaman publik (tanpa login) → masjid pertama yang ada di database.
     * - Fallback terakhir 1 supaya aplikasi tidak pernah kehilangan konteks.
     */
    public static function mosqueId(): int
    {
    $user = auth()->user();

    if ($user && $user->role === 'Super Admin') {
        return (int) (session('active_mosque_id')
            ?? Mosque::orderBy('id')->value('id')
            ?? 1);
    }

    return (int) ($user->mosque_id
        ?? Mosque::orderBy('id')->value('id')
        ?? 1);
    }

    public static function setActiveMosque(int $mosqueId): void
    {
    session(['active_mosque_id' => $mosqueId]);
    }
}
