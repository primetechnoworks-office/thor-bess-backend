<?php

namespace App\Models;

use App\Models\Concerns\RefreshesWebsite;
use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Model;

/** Grant / work-order card in the About page's "Milestones" section. */
class Milestone extends Model
{
    use RefreshesWebsite, Sortable;

    protected static string $websiteTag = 'milestones';

    protected $fillable = ['tag', 'title', 'copy', 'badge', 'note', 'status', 'image', 'sort_order', 'is_active'];
}
