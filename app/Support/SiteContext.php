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
        return (int) (auth()->user()->mosque_id
            ?? Mosque::orderBy('id')->value('id')
            ?? 1);
    }
}
