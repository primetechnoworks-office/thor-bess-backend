<?php

namespace App\Models;

use App\Models\Concerns\RefreshesWebsite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Customer quote in the "What our clients say" slider (home and about pages). */
class Testimonial extends Model
{
    use RefreshesWebsite;

    protected static string $websiteTag = 'testimonials';

    protected $fillable = ['quote', 'name', 'role', 'avatar', 'sort_order', 'is_active'];

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
}
