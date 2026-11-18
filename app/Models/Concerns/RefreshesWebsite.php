<?php

namespace App\Models\Concerns;

use App\Support\WebsiteCache;

/**
 * Tells the website to refresh its cached copy whenever a record is saved or deleted.
 * The using model sets: protected static string $websiteTag = 'products';
 */
trait RefreshesWebsite
{
    protected static function bootRefreshesWebsite(): void
    {
        $refresh = fn () => WebsiteCache::flush(static::$websiteTag);
        static::saved($refresh);
        static::deleted($refresh);
    }
}
