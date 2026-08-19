<?php

namespace App\Models\Concerns;

use App\Models\Mosque;
use App\Support\SiteContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Data milik satu masjid: relasi, query scope, dan route-model binding.
 * Binding `{pengumuman}` / `{kegiatan}` otomatis 404 jika record-nya masjid lain.
 */
trait BelongsToMosque
{
    public function mosque(): BelongsTo
    {
        return $this->belongsTo(Mosque::class);
    }

    public function scopeForMosque(Builder $query, ?int $mosqueId = null): Builder
    {
        return $query->where(
            $query->qualifyColumn('mosque_id'),
            $mosqueId ?? SiteContext::mosqueId(),
        );
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $field ??= $this->getRouteKeyName();

        return $this->newQuery()
            ->forMosque()
            ->where($field, $value)
            ->firstOrFail();
    }
}
