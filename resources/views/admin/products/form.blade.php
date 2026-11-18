@extends('layouts.app')
@section('title', $product->exists ? 'Edit product' : 'Add product')
@section('content')
@php
   $variants = old('variants', $product->variants ?: [['label' => '', 'capacity_ah' => '', 'energy' => '', 'price' => '']]);
   $images = collect(old('images', $product->images ?? []))->map(fn ($path) => ['path' => $path, 'url' => \App\Support\Media::forAdmin($path)])->values();
@endphp
<x-page-head :title="$product->exists ? $product->name : 'Add product'" :crumbs="['Products' => route('admin.products.index'), ($product->exists ? 'Edit' : 'New') => null]">
   @if ($product->exists && $product->is_active)
      <a href="{{ config('thor.frontend_url') }}/products/lfp-battery/{{ $product->slug }}" target="_blank" rel="noopener" class="t-btn t-btn--outline"><x-icon name="external" /> View on website</a>
   @endif
</x-page-head>

<form method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" class="t-grid t-grid--main">
   @csrf
   @if ($product->exists) @method('PUT') @endif

   <div class="t-stack">
      <section class="t-card">
         <div class="t-card__head"><h2>Product details</h2></div>
         <div class="t-card__body">
            <x-input name="name" label="Product name" :value="$product->name" required maxlength="150" placeholder='e.g. "ThOR" LFP 12V 100Ah'
                     :help="$product->exists ? 'Web address: /products/lfp-battery/'.$product->slug.' — stays the same if you rename the product.' : 'The web address is created from the name when you save.'" />
            <div class="t-form-grid" style="margin-top:18px">
               <div class="t-field">
                  <label for="f-series" class="t-label">Series <span class="req">*</span></label>
                  <select id="f-series" name="series" class="t-select">
                     @foreach (array_keys(\App\Models\Product::SERIES) as $series)
                        <option value="{{ $series }}" @selected(old('series', $product->series) === $series)>{{ $series }}</option>
                     @endforeach
                  </select>
                  <span class="t-help">Used for the website's 12V / 24V / 48V filter.</span>
               </div>
               <x-input name="capacity_label" label="Capacity label" :value="$product->capacity_label" required maxlength="40" placeholder="e.g. 100 Ah or 15 / 30 Ah" />
            </div>
            <div class="t-field">
               <label for="f-summary" class="t-label">Summary <span class="req">*</span></label>
               <textarea id="f-summary" name="summary" rows="3" maxlength="600" required class="t-textarea @error('summary') is-invalid @enderror" placeholder="One or two sentences shown on the product card and page.">{{ old('summary', $product->summary) }}</textarea>
               @error('summary')<span class="t-error">{{ $message }}</span>@enderror
            </div>
            <div class="t-field">
               <label for="f-best" class="t-label">Best for</label>
               <textarea id="f-best" name="best_for" rows="4" maxlength="1000" class="t-textarea @error('best_for') is-invalid @enderror" placeholder="Home inverter backup&#10;Rooftop solar">{{ old('best_for', implode("\n", $product->best_for ?? [])) }}</textarea>
               @error('best_for')<span class="t-error">{{ $message }}</span>@else<span class="t-help">One use per line — shown as the "Best for" list.</span>@enderror
            </div>
            <x-input name="model" label="Model number" :value="$product->model" maxlength="80" placeholder="Optional, e.g. THOR-PRO-48-300H-15K" />
         </div>
      </section>

      <section class="t-card" x-data="{ rows: @js(array_values($variants)), add() { this.rows.push({ label: '', capacity_ah: '', energy: '', price: '' }) } }">
         <div class="t-card__head">
            <div><h2>Sizes &amp; prices</h2><p>Each size is a choice on the product page. The lowest price shows as "From ₹…".</p></div>
            <button type="button" class="t-btn t-btn--outline t-btn--sm" @click="add()"><x-icon name="plus" /> Add size</button>
         </div>
         <div class="t-card__body">
            @if ($errors->has('variants') || $errors->has('variants.*'))
               <div class="t-alert t-alert--error" style="margin-bottom:16px"><x-icon name="alert" /><span>{{ $errors->first('variants') ?: collect($errors->get('variants.*'))->flatten()->first() }}</span></div>
            @endif
            <div class="t-variants">
               <div class="t-variants__head"><span>Size label</span><span>Capacity (Ah)</span><span>Energy</span><span>Price (₹)</span><span></span></div>
               <template x-for="(row, i) in rows" :key="i">
                  <div class="t-variants__row">
                     <input class="t-input" :name="`variants[${i}][label]`" x-model="row.label" placeholder="100 Ah" maxlength="40" required aria-label="Size label">
                     <input class="t-input" type="number" step="any" min="0" :name="`variants[${i}][capacity_ah]`" x-model="row.capacity_ah" placeholder="100" required aria-label="Capacity in Ah">
                     <input class="t-input" :name="`variants[${i}][energy]`" x-model="row.energy" placeholder="1.4 kWh" maxlength="60" required aria-label="Energy">
                     <input class="t-input" type="number" min="0" step="1" :name="`variants[${i}][price]`" x-model="row.price" placeholder="19799" required aria-label="Price in rupees">
                     <button type="button" class="t-icon-action t-icon-action--danger" title="Remove size" @click="rows.splice(i, 1)" :disabled="rows.length === 1"><x-icon name="trash" /></button>
                  </div>
               </template>
            </div>
         </div>
      </section>

      <section class="t-card" x-data="{
            images: @js($images),
            fresh: [],
            move(i, d) { const j = i + d; if (j < 0 || j >= this.images.length) return; [this.images[i], this.images[j]] = [this.images[j], this.images[i]]; },
            pick(e) { this.fresh = [...e.target.files].map(f => ({ name: f.name, url: URL.createObjectURL(f) })) },
         }">
         <div class="t-card__head"><div><h2>Photos</h2><p>The first photo is the cover on product cards. Use the arrows to reorder.</p></div></div>
         <div class="t-card__body">
            <div class="t-gallery">
               <template x-for="(img, i) in images" :key="img.path">
                  <figure class="t-gallery__item">
                     <img :src="img.url" alt="" loading="lazy">
                     <input type="hidden" name="images[]" :value="img.path">
                     <span class="t-gallery__badge" x-show="i === 0">Cover</span>
                     <figcaption>
                        <button type="button" title="Move left" @click="move(i, -1)" :disabled="i === 0"><x-icon name="chevron-left" size="16" /></button>
                        <button type="button" title="Move right" @click="move(i, 1)" :disabled="i === images.length - 1"><x-icon name="chevron-right" size="16" /></button>
                        <button type="button" title="Remove photo" class="is-danger" @click="images.splice(i, 1)"><x-icon name="trash" size="16" /></button>
                     </figcaption>
                  </figure>
               </template>
               <template x-for="f in fresh" :key="f.url">
                  <figure class="t-gallery__item is-new"><img :src="f.url" alt=""><span class="t-gallery__badge">New</span></figure>
               </template>
            </div>
            <div class="t-field" style="margin-top:16px">
               <label for="f-images" class="t-label">Add photos</label>
               <input id="f-images" type="file" name="new_images[]" multiple accept="image/jpeg,image/png,image/webp" class="t-input @error('new_images') is-invalid @enderror @error('new_images.*') is-invalid @enderror" style="padding:8px 10px" @change="pick($event)">
               @if ($errors->has('new_images') || $errors->has('new_images.*'))
                  <span class="t-error">{{ $errors->first('new_images') ?: collect($errors->get('new_images.*'))->flatten()->first() }}</span>
               @else
                  <span class="t-help">JPG, PNG or WebP, up to 3 MB each. New photos are added after the existing ones. Removed photos are deleted when you save.</span>
               @endif
            </div>
         </div>
      </section>
   </div>

   <div class="t-stack">
      <section class="t-card">
         <div class="t-card__head"><h2>Publishing</h2></div>
         <div class="t-card__body">
            <label class="t-switch">
               <input type="hidden" name="is_active" value="0">
               <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))>
               <span class="t-switch__track"></span>
               Show on website
            </label>
            <span class="t-help">Hidden products disappear from the catalogue and the datasheet form.</span>
            <div style="margin-top:18px">
               <x-input name="sort_order" type="number" label="Display order" :value="$product->sort_order" required min="0" help="Lower numbers show first (10, 20, 30…)." />
            </div>
         </div>
         <div class="t-card__foot">
            <a href="{{ route('admin.products.index') }}" class="t-btn t-btn--ghost">Cancel</a>
            <button type="submit" class="t-btn">{{ $product->exists ? 'Save changes' : 'Create product' }}</button>
         </div>
      </section>

      <section class="t-card">
         <div class="t-card__head"><h2>Specifications</h2></div>
         <div class="t-card__body">
            <div class="t-form-grid">
               <x-input name="cycles" type="number" label="Cycle life" :value="$product->cycles" required min="0" />
               <x-input name="service_life" type="number" label="Service life (years)" :value="$product->service_life" required min="0" />
               <x-input name="guarantee" type="number" label="Guarantee (years)" :value="$product->guarantee" required min="0" />
               <x-input name="warranty" type="number" label="Warranty (years)" :value="$product->warranty" required min="0" />
            </div>
         </div>
      </section>
   </div>
</form>
@endsection
