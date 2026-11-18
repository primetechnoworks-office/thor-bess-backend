@extends('layouts.app')
@section('title', $milestone->exists ? 'Edit milestone' : 'Add milestone')
@section('content')
<x-page-head :title="$milestone->exists ? 'Edit milestone' : 'Add milestone'" :crumbs="['Milestones' => route('admin.milestones.index'), ($milestone->exists ? 'Edit' : 'New') => null]" />

<form method="POST" enctype="multipart/form-data" action="{{ $milestone->exists ? route('admin.milestones.update', $milestone) : route('admin.milestones.store') }}" class="t-grid t-grid--main">
   @csrf
   @if ($milestone->exists) @method('PUT') @endif

   <section class="t-card">
      <div class="t-card__head"><h2>Milestone</h2></div>
      <div class="t-card__body">
         <div class="t-form-grid">
            <x-input name="tag" label="Sector tag" :value="$milestone->tag" required maxlength="60" placeholder="e.g. Defence" help="Small label above the title." />
            <x-input name="status" label="Status on photo" :value="$milestone->status" maxlength="80" placeholder="e.g. Work order ongoing" />
         </div>
         <div style="margin-top:18px">
            <x-input name="title" label="Title" :value="$milestone->title" required maxlength="160" placeholder="e.g. Tactical Power for Defence Mobility" />
         </div>
         <div class="t-field">
            <label for="f-copy" class="t-label">Description <span class="req">*</span></label>
            <textarea id="f-copy" name="copy" rows="4" maxlength="600" required class="t-textarea @error('copy') is-invalid @enderror">{{ old('copy', $milestone->copy) }}</textarea>
            @error('copy')<span class="t-error">{{ $message }}</span>@enderror
         </div>
         <div class="t-form-grid" style="margin-top:18px">
            <x-input name="badge" label="Badge" :value="$milestone->badge" maxlength="120" placeholder="e.g. iDEX (MoD) grant" help="Bold line at the bottom of the card." />
            <x-input name="note" label="Note" :value="$milestone->note" maxlength="255" placeholder="e.g. Ministry of Defence · 2026" help="Small print under the badge." />
         </div>
      </div>
   </section>

   <div class="t-stack">
      <x-photo-card name="photo" label="Photo" :url="\App\Support\Media::forAdmin($milestone->image)" help="Landscape (4:3) works best. JPG, PNG or WebP up to 3 MB." />
      <section class="t-card">
         <div class="t-card__head"><h2>Publishing</h2></div>
         <div class="t-card__body">
            <label class="t-switch">
               <input type="hidden" name="is_active" value="0">
               <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $milestone->is_active))>
               <span class="t-switch__track"></span>
               Show on website
            </label>
            <div style="margin-top:18px">
               <x-input name="sort_order" type="number" label="Display order" :value="$milestone->sort_order" required min="0" help="Lower numbers show first." />
            </div>
         </div>
         <div class="t-card__foot">
            <a href="{{ route('admin.milestones.index') }}" class="t-btn t-btn--ghost">Cancel</a>
            <button type="submit" class="t-btn">{{ $milestone->exists ? 'Save changes' : 'Add milestone' }}</button>
         </div>
      </section>
   </div>
</form>
@endsection
