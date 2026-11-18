@extends('layouts.app')
@section('title', $partner->exists ? 'Edit partner' : 'Add partner')
@section('content')
<x-page-head :title="$partner->exists ? $partner->name : 'Add partner logo'" :crumbs="['Partners' => route('admin.partners.index'), ($partner->exists ? 'Edit' : 'New') => null]" />

<form method="POST" enctype="multipart/form-data" action="{{ $partner->exists ? route('admin.partners.update', $partner) : route('admin.partners.store') }}" class="t-grid t-grid--main">
   @csrf
   @if ($partner->exists) @method('PUT') @endif

   <section class="t-card">
      <div class="t-card__head"><h2>Partner</h2></div>
      <div class="t-card__body">
         <x-input name="name" label="Partner name" :value="$partner->name" required maxlength="160" placeholder="e.g. TiHAN IIT Hyderabad" help="Also used as the logo's description for screen readers." />
         <x-input name="url" type="url" label="Website link" :value="$partner->url" maxlength="255" placeholder="https://…" help="Optional. Clicking the logo opens this page." />
         <div style="margin-top:18px">
            <label class="t-switch">
               <input type="hidden" name="is_active" value="0">
               <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $partner->is_active))>
               <span class="t-switch__track"></span>
               Show on website
            </label>
         </div>
         <div style="margin-top:18px">
            <x-input name="sort_order" type="number" label="Display order" :value="$partner->sort_order" required min="0" help="Lower numbers show first in the slider." />
         </div>
      </div>
      <div class="t-card__foot">
         <a href="{{ route('admin.partners.index') }}" class="t-btn t-btn--ghost">Cancel</a>
         <button type="submit" class="t-btn">{{ $partner->exists ? 'Save changes' : 'Add partner' }}</button>
      </div>
   </section>

   <x-photo-card name="photo" label="Logo" :url="\App\Support\Media::forAdmin($partner->logo)" :removable="false" contain
                 help="Wide logo on a white or transparent background. JPG, PNG or WebP up to 2 MB (export SVG logos as PNG)." />
</form>
@endsection
