<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Milestone;
use App\Models\Partner;
use App\Models\Product;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Support\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Website content managed in the admin panel. Only live (active) records are returned. */
class SiteController extends Controller
{
    public function settings(): JsonResponse
    {
        return $this->ok(Setting::get(Setting::COMPANY));
    }

    public function products(): JsonResponse
    {
        return $this->ok(Product::active()->ordered()->get()->map(fn (Product $p) => $this->present($p)));
    }

    public function product(string $slug): JsonResponse
    {
        $product = Product::active()->where('slug', $slug)->first();

        return $product
            ? $this->ok($this->present($product))
            : response()->json(['ok' => false, 'error' => 'Product not found.'], 404);
    }

    /** Value/label list for the website's "Request datasheet" form. */
    public function datasheetOptions(): JsonResponse
    {
        return $this->ok(collect(Product::datasheetOptions())->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values());
    }

    public function testimonials(): JsonResponse
    {
        return $this->ok(Testimonial::active()->ordered()->get()->map(fn (Testimonial $t) => [
            'quote' => $t->quote, 'name' => $t->name, 'role' => $t->role, 'avatar' => Media::forWebsite($t->avatar),
        ]));
    }

    public function team(): JsonResponse
    {
        return $this->ok(TeamMember::active()->ordered()->get()->map(fn (TeamMember $m) => [
            'name' => $m->name, 'role' => $m->role, 'image' => Media::forWebsite($m->image), 'linkedin' => $m->linkedin ?? '',
        ]));
    }

    public function milestones(): JsonResponse
    {
        return $this->ok(Milestone::active()->ordered()->get()->map(fn (Milestone $m) => [
            'tag' => $m->tag, 'title' => $m->title, 'copy' => $m->copy, 'badge' => $m->badge ?? '',
            'note' => $m->note ?? '', 'status' => $m->status ?? '', 'image' => Media::forWebsite($m->image),
        ]));
    }

    public function faqs(Request $request): JsonResponse
    {
        $page = (string) $request->query('page', '');

        return $this->ok(Faq::active()->when($page, fn ($q) => $q->where('page', $page))->ordered()->get()
            ->map(fn (Faq $f) => ['page' => $f->page, 'q' => $f->question, 'a' => $f->answer]));
    }

    /** Partner logos (home slider) + incubators list (About page). */
    public function partners(): JsonResponse
    {
        return $this->ok([
            'logos' => Partner::active()->ordered()->get()->map(fn (Partner $p) => [
                'src' => Media::forWebsite($p->logo), 'alt' => $p->name, 'href' => $p->url ?? '',
            ]),
            'incubators' => Setting::get(Setting::INCUBATORS),
        ]);
    }

    public function credentials(): JsonResponse
    {
        $c = Setting::get(Setting::CREDENTIALS);
        $c['certificate'] = Media::forWebsite($c['certificate'] ?? null);
        $c['certificate_hd'] = Media::forWebsite($c['certificate_hd'] ?? null);

        return $this->ok($c);
    }

    /** Home hero film + inner-page banner images. */
    public function media(): JsonResponse
    {
        $hero = Setting::get(Setting::HERO);

        return $this->ok([
            'hero' => [
                'youtubeId' => $hero['youtube_id'] ?? '',
                'file' => Media::forWebsite($hero['video'] ?? null) ?? '',
                'poster' => Media::forWebsite($hero['poster'] ?? null) ?? '',
            ],
            'banners' => array_map(fn ($path) => Media::forWebsite($path), Setting::get(Setting::BANNERS)),
        ]);
    }

    /** Same shape as the website's LfpProduct type (frontend/app/data/products.ts). */
    private function present(Product $p): array
    {
        return [
            'slug' => $p->slug, 'name' => $p->name, 'series' => $p->series, 'voltage' => $p->voltage,
            'capacityLabel' => $p->capacity_label,
            'variants' => array_map(fn ($v) => ['label' => $v['label'], 'capacityAh' => $v['capacity_ah'], 'energy' => $v['energy'], 'price' => $v['price']], $p->variants),
            'cycles' => $p->cycles, 'serviceLife' => $p->service_life, 'guarantee' => $p->guarantee, 'warranty' => $p->warranty,
            'summary' => $p->summary, 'bestFor' => $p->best_for, 'model' => $p->model,
            'images' => array_map(fn ($path) => Media::forWebsite($path), $p->images),
        ];
    }

    private function ok(mixed $data): JsonResponse
    {
        return response()->json(['ok' => true, 'data' => $data]);
    }
}
