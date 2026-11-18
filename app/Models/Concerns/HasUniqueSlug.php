<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/** Web-address slugs: name → "name", then "name-2", "name-3", … if taken. */
trait HasUniqueSlug
{
    public static function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: Str::singular((new static)->getTable());
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
