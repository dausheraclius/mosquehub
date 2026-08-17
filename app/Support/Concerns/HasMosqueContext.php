<?php

namespace App\Support\Concerns;

use App\Support\SiteContext;

/**
 * Memberi properti `$mosqueId` yang diisi dari SiteContext di constructor.
 * Dipakai semua controller area terautentikasi agar tidak mengulang
 * `private int $mosqueId; public function __construct() { ... }`.
 */
trait HasMosqueContext
{
    protected int $mosqueId;

    public function __construct()
    {
        $this->mosqueId = SiteContext::mosqueId();
    }
}
