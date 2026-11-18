<?php

namespace App\Models;

use App\Models\Concerns\RefreshesWebsite;
use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Model;

/** Question & answer shown on a website page (pages listed in config/thor.php → faq_pages). */
class Faq extends Model
{
    use RefreshesWebsite, Sortable;

    protected static string $websiteTag = 'faqs';

    protected $fillable = ['page', 'question', 'answer', 'sort_order', 'is_active'];

    public function pageLabel(): string
    {
        return config('thor.faq_pages.'.$this->page, $this->page);
    }
}
