<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use App\Models\Concerns\RefreshesWebsite;
use App\Support\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** An LFP battery shown on the website's /products/lfp-battery pages. */
class Product extends Model
{
    use HasUniqueSlug, RefreshesWebsite;

    protected static string $websiteTag = 'products';

    public const SERIES = ['12V' => 12, '24V' => 24, '48V' => 48];

    protected $fillable = [
        'slug', 'name', 'series', 'voltage', 'capacity_label', 'variants', 'cycles', 'service_life',
        'guarantee', 'warranty', 'model', 'summary', 'best_for', 'images', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'variants' => 'array',
            'best_for' => 'array',
            'images' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function fromPrice(): ?int
    {
        $prices = array_filter(array_column($this->variants ?? [], 'price'), fn ($p) => $p !== null && $p !== '');

        return $prices ? (int) min($prices) : null;
    }

    public function coverUrl(): ?string
    {
        return Media::forAdmin($this->images[0] ?? null);
    }

    /**
     * Options for the website's "Request datasheet" form: every live battery plus the
     * e-Generator and container (config/thor.php → datasheet_extra). value => label.
     *
     * @return array<string, string>
     */
    public static function datasheetOptions(): array
    {
        return static::active()->ordered()->pluck('name', 'slug')->all() + config('thor.datasheet_extra');
    }
}
