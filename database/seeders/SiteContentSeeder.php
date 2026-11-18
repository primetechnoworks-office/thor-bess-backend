<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Milestone;
use App\Models\Partner;
use App\Models\Product;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/**
 * Imports the content currently hard-coded in the website (frontend/app/data/*.ts), exported to
 * database/seeders/data/*.json. Safe to re-run: existing records are left as they are.
 */
class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $data = fn (string $name) => json_decode(file_get_contents(database_path("seeders/data/{$name}.json")), true);

        foreach ($data('products') as $row) {
            Product::firstOrCreate(['slug' => $row['slug']], $row + ['is_active' => true]);
        }

        if (! Testimonial::exists()) {
            foreach ($data('testimonials') as $row) {
                Testimonial::create($row + ['is_active' => true]);
            }
        }

        if (! TeamMember::exists()) {
            foreach ($data('team') as $row) {
                TeamMember::create($row + ['is_active' => true]);
            }
        }

        if (! Setting::find(Setting::COMPANY)) {
            Setting::put(Setting::COMPANY, $data('settings'));
        }

        // Phase 2
        foreach (['milestones' => Milestone::class, 'faqs' => Faq::class, 'partners' => Partner::class] as $file => $model) {
            if (! $model::exists()) {
                foreach ($data($file) as $row) {
                    $model::create($row + ['is_active' => true]);
                }
            }
        }
        foreach ([Setting::INCUBATORS => 'incubators', Setting::CREDENTIALS => 'credentials', Setting::HERO => 'hero', Setting::BANNERS => 'banners'] as $key => $file) {
            if (! Setting::find($key)) {
                Setting::put($key, $data($file));
            }
        }
    }
}
