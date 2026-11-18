<?php

namespace App\Models;

use App\Support\WebsiteCache;
use Illuminate\Database\Eloquent\Model;

/**
 * Key/value site settings. Company details live under the "company" key:
 * phone, whatsapp, email, sales_email, landline, fax, hours, address[], offices[],
 * google_profile, google_review, socials{facebook, instagram, x, linkedin, youtube, pinterest},
 * book_now_url, indiamart_url.
 */
class Setting extends Model
{
    public const COMPANY = 'company';
    public const CREDENTIALS = 'credentials';   // registrations, patent, awards, certificate image
    public const INCUBATORS = 'incubators';     // list of incubation partners (About page)
    public const HERO = 'hero';                 // home page film: youtube_id, video, poster
    public const BANNERS = 'banners';           // inner-page banner image per page key

    /** Which website cache to refresh when a key changes (app/lib/backend.ts → ContentTag). */
    private const TAGS = [
        self::COMPANY => 'settings', self::CREDENTIALS => 'credentials', self::INCUBATORS => 'partners',
        self::HERO => 'media', self::BANNERS => 'media',
    ];

    public const SOCIAL_NETWORKS = [
        'facebook' => 'Facebook', 'instagram' => 'Instagram', 'x' => 'X (Twitter)',
        'linkedin' => 'LinkedIn', 'youtube' => 'YouTube', 'pinterest' => 'Pinterest',
    ];

    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    protected function casts(): array
    {
        return ['value' => 'array'];
    }

    public static function get(string $key, array $default = []): array
    {
        return static::find($key)?->value ?? $default;
    }

    public static function put(string $key, array $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        WebsiteCache::flush(self::TAGS[$key] ?? 'settings');
    }
}
