<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Tells the Next.js website to drop its cached copy of some data (POST {FRONTEND_URL}/api/revalidate),
 * so admin edits show up immediately. Without WEBSITE_REVALIDATE_SECRET the site simply refreshes
 * on its own every 5 minutes.
 */
class WebsiteCache
{
    public static function flush(string $tag): void
    {
        $secret = config('thor.revalidate_secret');
        if (! $secret) {
            return;
        }

        try {
            Http::withToken($secret)->acceptJson()->connectTimeout(2)->timeout(5)
                ->post(config('thor.frontend_url').'/api/revalidate', ['tag' => $tag])
                ->throw();
        } catch (\Throwable $e) {
            // The website will still refresh on its timer — never block the admin over this.
            Log::warning("Website cache refresh for [{$tag}] failed: {$e->getMessage()}");
        }
    }
}
