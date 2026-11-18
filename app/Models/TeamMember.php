<?php

namespace App\Models;

use App\Models\Concerns\RefreshesWebsite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Leadership team card on the website's About page. */
class TeamMember extends Model
{
    use RefreshesWebsite;

    protected static string $websiteTag = 'team';

    protected $fillable = ['name', 'role', 'image', 'linkedin', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function initials(): string
    {
        return collect(preg_split('/\s+/', trim(preg_replace('/^(Mr|Ms|Mrs|Dr)\.?\s+/i', '', $this->name))))
            ->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    }
}
