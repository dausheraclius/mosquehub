<?php

namespace App\Support;

class MediaHelper
{
    /**
     * Ubah URL media absolut (dibangun dari APP_URL, mis. http://localhost/...)
     * menjadi path relatif (/storage/...) supaya gambar/file ikut host & port
     * yang sedang dipakai browser. Mengikuti konvensi JamaahController.
     */
    public static function relativeUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);

        return $path ?: null;
    }
}
