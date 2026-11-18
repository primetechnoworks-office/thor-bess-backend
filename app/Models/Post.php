<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use App\Support\PostBody;
use App\Support\WebsiteCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasUniqueSlug;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'title', 'slug', 'category', 'excerpt', 'body', 'image', 'image_alt',
        'author', 'status', 'is_featured', 'published_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'published_at' => 'date',
        ];
    }

    /** Bodies are always stored as cleaned HTML (old text-format bodies are converted). */
    protected function body(): Attribute
    {
        return Attribute::make(set: fn (?string $value) => PostBody::normalize($value));
    }

    protected static function booted(): void
    {
        $refreshWebsite = fn () => WebsiteCache::flush('posts');
        static::saved($refreshWebsite);
        static::deleted($refreshWebsite);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Live on the website: published and not scheduled for the future. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->whereDate('published_at', '<=', now());
    }

    /**
     * Absolute image URL. Uploaded images live on the public disk;
     * seeded images are website paths (/images/...) served by the Next.js site.
     */
    public function imageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }
        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }
        if (Str::startsWith($this->image, '/')) {
            return config('thor.frontend_url').$this->image;
        }

        return Storage::disk('public')->url($this->image);
    }

    public function isUploadedImage(): bool
    {
        return $this->image && ! Str::startsWith($this->image, ['/', 'http://', 'https://']);
    }

    public function readingTime(): string
    {
        $words = str_word_count(strip_tags($this->body));

        return max(2, (int) round($words / 200)).' min read';
    }

    public function isLive(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && $this->published_at && $this->published_at->lte(now());
    }
}
