<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'series' => ['nullable', Rule::in(array_keys(Product::SERIES))],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $products = Product::query()
            ->when($filters['series'] ?? null, fn ($q, $s) => $q->where('series', $s))
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->ordered()->get();

        return view('admin.products.index', ['products' => $products, 'filters' => $filters]);
    }

    public function create(): View
    {
        return view('admin.products.form', ['product' => new Product([
            'series' => '12V', 'cycles' => 4000, 'service_life' => 9, 'guarantee' => 3, 'warranty' => 5, 'is_active' => true,
            'sort_order' => (int) Product::max('sort_order') + 10,
            'variants' => [['label' => '', 'capacity_ah' => '', 'energy' => '', 'price' => '']], 'best_for' => [], 'images' => [],
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Product::uniqueSlug($data['name']);

        $product = Product::create($data);

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product created.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', ['product' => $product]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        // The slug stays fixed: it's the product's web address and the datasheet form's value.
        $product->update($this->validated($request, $product));

        return back()->with('success', 'Product saved.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        foreach ($product->images ?? [] as $path) {
            Media::delete($path);
        }
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'series' => ['required', Rule::in(array_keys(Product::SERIES))],
            'capacity_label' => ['required', 'string', 'max:40'],
            'model' => ['nullable', 'string', 'max:80'],
            'summary' => ['required', 'string', 'max:600'],
            'best_for' => ['nullable', 'string', 'max:1000'],
            'cycles' => ['required', 'integer', 'min:0', 'max:100000'],
            'service_life' => ['required', 'integer', 'min:0', 'max:50'],
            'guarantee' => ['required', 'integer', 'min:0', 'max:50'],
            'warranty' => ['required', 'integer', 'min:0', 'max:50'],
            'variants' => ['required', 'array', 'min:1', 'max:10'],
            'variants.*.label' => ['required', 'string', 'max:40'],
            'variants.*.capacity_ah' => ['required', 'numeric', 'min:0', 'max:100000'],
            'variants.*.energy' => ['required', 'string', 'max:60'],
            'variants.*.price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'images' => ['nullable', 'array'],
            'images.*' => ['string', Rule::in($product?->images ?? [])], // only reorder/remove existing ones
            'new_images' => ['nullable', 'array', 'max:20'],
            'new_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'variants.required' => 'Add at least one size / price.',
            'variants.*.label.required' => 'Each size needs a label (e.g. "100 Ah").',
            'variants.*.price.required' => 'Each size needs a price.',
            'variants.*.price.integer' => 'Prices must be whole rupees (no commas or decimals).',
            'new_images.*.max' => 'Each image must be 3 MB or smaller.',
        ]);

        // Images: kept ones in the chosen order, then new uploads. Removed uploads are deleted.
        $kept = array_values($data['images'] ?? []);
        if (! $kept && ! $request->file('new_images')) {
            throw ValidationException::withMessages(['new_images' => 'Add at least one product photo.']);
        }
        foreach (array_diff($product?->images ?? [], $kept) as $removed) {
            Media::delete($removed);
        }
        foreach ($request->file('new_images', []) as $file) {
            $kept[] = Media::store($file, 'products');
        }

        unset($data['new_images']);
        $data['images'] = $kept;
        $data['voltage'] = Product::SERIES[$data['series']];
        $data['best_for'] = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) ($data['best_for'] ?? ''))))) ;
        $data['variants'] = array_values(array_map(fn ($v) => [
            'label' => trim($v['label']), 'capacity_ah' => $v['capacity_ah'] + 0, 'energy' => trim($v['energy']), 'price' => (int) $v['price'],
        ], $data['variants']));
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
