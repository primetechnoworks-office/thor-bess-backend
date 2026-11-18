@extends('layouts.app')
@section('title', $testimonial->exists ? 'Edit testimonial' : 'Add testimonial')
@section('content')
<x-page-head :title="$testimonial->exists ? 'Edit testimonial' : 'Add testimonial'" :crumbs="['Testimonials' => route('admin.testimonials.index'), ($testimonial->exists ? 'Edit' : 'New') => null]" />

<form method="POST" enctype="multipart/form-data" action="{{ $testimonial->exists ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}" class="t-grid t-grid--main">
   @csrf
   @if ($testimonial->exists) @method('PUT') @endif

   <section class="t-card">
      <div class="t-card__head"><h2>Testimonial</h2></div>
      <div class="t-card__body">
         <div class="t-field">
            <label for="f-quote" class="t-label">Quote <span class="req">*</span></label>
            <textarea id="f-quote" name="quote" rows="5" maxlength="360" required class="t-textarea @error('quote') is-invalid @enderror" placeholder="What the client said about “ThOR” BESS…">{{ old('quote', $testimonial->quote) }}</textarea>
            @error('quote')<span class="t-error">{{ $message }}</span>@else<span class="t-help">Up to 360 characters so the slider stays a steady height. Quotation marks are added by the website.</span>@enderror
         </div>
         <div class="t-form-grid" style="margin-top:18px">
            <x-input name="name" label="Client name" :value="$testimonial->name" required maxlength="60" placeholder="e.g. Priya Sharma" />
            <x-input name="role" label="Role & company" :value="$testimonial->role" maxlength="80" placeholder="e.g. Operations Head, Sunrise Hospitals" />
         </div>
      </div>
   </section>

   <div class="t-stack">
      <x-photo-card name="photo" label="Client photo" :max-mb="2" :url="\App\Support\Media::forAdmin($testimonial->avatar)" help="Square photo works best. JPG, PNG or WebP up to 2 MB." round />
      <section class="t-card">
         <div class="t-card__head"><h2>Publishing</h2></div>
         <div class="t-card__body">
            <label class="t-switch">
               <input type="hidden" name="is_active" value="0">
               <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $testimonial->is_active))>
               <span class="t-switch__track"></span>
               Show on website
            </label>
            <div style="margin-top:18px">
               <x-input name="sort_order" type="number" label="Display order" :value="$testimonial->sort_order" required min="0" help="Lower numbers show first in the slider." />
            </div>
         </div>
         <div class="t-card__foot">
            <a href="{{ route('admin.testimonials.index') }}" class="t-btn t-btn--ghost">Cancel</a>
            <button type="submit" class="t-btn">{{ $testimonial->exists ? 'Save changes' : 'Add testimonial' }}</button>
         </div>
      </section>
   </div>
</form>
@endsection
