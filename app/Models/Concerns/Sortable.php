<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/** Shared by content lists that have `is_active` and `sort_order` columns. */
trait Sortable
{
    public function initializeSortable(): void
    {
        $this->mergeCasts(['is_active' => 'boolean']);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /** Default "Display order" for a new record: after the last one. */
    public static function nextSortOrder(): int
    {
        return (int) static::max('sort_order') + 10;
    }
}
