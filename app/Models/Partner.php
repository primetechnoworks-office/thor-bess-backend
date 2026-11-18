<?php

namespace App\Models;

use App\Models\Concerns\RefreshesWebsite;
use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Model;

/** Logo in the home page's partner / technology slider. */
class Partner extends Model
{
    use RefreshesWebsite, Sortable;

    protected static string $websiteTag = 'partners';

    protected $fillable = ['name', 'logo', 'url', 'sort_order', 'is_active'];
}
