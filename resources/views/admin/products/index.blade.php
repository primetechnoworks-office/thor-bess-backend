@extends('layouts.app')
@section('title', 'Products')
@section('content')
<x-page-head title="LFP Batteries" kicker="Products" description="The battery catalogue on the website, with sizes, prices and photos.">
   <a href="{{ route('admin.products.create') }}" class="t-btn"><x-icon name="plus" /> Add product</a>
</x-page-head>

<section class="t-card">
   <div class="t-card__head">
      <form method="GET" class="t-filters" style="width:100%">
         <div class="t-input-wrap">
            <x-icon name="search" />
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search products…" class="t-input t-input--icon">
         </div>
         <select name="series" class="t-select" onchange="this.form.submit()" aria-label="Series">
            <option value="">All series</option>
            @foreach (array_keys(\App\Models\Product::SERIES) as $series)
               <option value="{{ $series }}" @selected(($filters['series'] ?? '') === $series)>{{ $series }} series</option>
            @endforeach
         </select>
         <button type="submit" class="t-btn t-btn--navy">Filter</button>
         @if (array_filter($filters))<a href="{{ route('admin.products.index') }}" class="t-btn t-btn--ghost">Clear</a>@endif
      </form>
   </div>

   @if ($products->isEmpty())
      <div class="t-empty">
         <span class="t-empty__icon"><x-icon name="battery" /></span>
         <h3>No products found</h3>
         <p>Add a battery and it will appear on the website's LFP Battery page.</p>
      </div>
   @else
      <div class="t-table-wrap">
         <table class="t-table">
            <thead><tr><th>Product</th><th>Series</th><th>Sizes</th><th>Price</th><th>Status</th><th>Order</th><th class="t-right">Actions</th></tr></thead>
            <tbody>
               @foreach ($products as $product)
                  <tr>
                     <td style="min-width:260px">
                        <div class="t-flex">
                           <img src="{{ $product->coverUrl() }}" alt="" class="t-thumb" style="object-fit:contain;background:#fff" loading="lazy" onerror="this.style.visibility='hidden'">
                           <span style="min-width:0">
                              <a href="{{ route('admin.products.edit', $product) }}" class="t-row-link">{{ $product->name }}</a>
                              <span class="t-sub">{{ $product->capacity_label }} · {{ count($product->images) }} {{ Str::plural('photo', count($product->images)) }}</span>
                           </span>
                        </div>
                     </td>
                     <td><span class="t-badge t-badge--navy t-badge--plain">{{ $product->series }}</span></td>
                     <td class="t-nowrap">{{ collect($product->variants)->pluck('label')->implode(', ') }}</td>
                     <td class="t-nowrap t-strong">
                        @if (count($product->variants) > 1)<span class="t-soft" style="font-weight:400">from</span> @endif
                        ₹{{ number_format($product->fromPrice() ?? 0) }}
                     </td>
                     <td><span class="t-badge {{ $product->is_active ? 't-badge--success' : 't-badge--neutral' }}">{{ $product->is_active ? 'Live' : 'Hidden' }}</span></td>
                     <td class="t-soft">{{ $product->sort_order }}</td>
                     <td class="t-right t-nowrap" x-data>
                        @if ($product->is_active)
                           <a href="{{ config('thor.frontend_url') }}/products/lfp-battery/{{ $product->slug }}" target="_blank" rel="noopener" class="t-icon-action" title="View on website"><x-icon name="external" /></a>
                        @endif
                        <a href="{{ route('admin.products.edit', $product) }}" class="t-icon-action" title="Edit"><x-icon name="pencil" /></a>
                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" style="display:inline" @submit="if (!confirm('Delete {{ addslashes($product->name) }}? Its page will be removed from the website. To hide it temporarily, untick “Show on website” instead.')) $event.preventDefault()">
                           @csrf @method('DELETE')
                           <button type="submit" class="t-icon-action t-icon-action--danger" title="Delete"><x-icon name="trash" /></button>
                        </form>
                     </td>
                  </tr>
               @endforeach
            </tbody>
         </table>
      </div>
   @endif
</section>
@endsection
