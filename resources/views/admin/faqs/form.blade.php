@extends('layouts.app')
@section('title', $faq->exists ? 'Edit question' : 'Add question')
@section('content')
<x-page-head :title="$faq->exists ? 'Edit question' : 'Add question'" :crumbs="['FAQs' => route('admin.faqs.index'), ($faq->exists ? 'Edit' : 'New') => null]" />

<form method="POST" action="{{ $faq->exists ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}" class="t-grid t-grid--main">
   @csrf
   @if ($faq->exists) @method('PUT') @endif

   <section class="t-card">
      <div class="t-card__head"><h2>Question &amp; answer</h2></div>
      <div class="t-card__body">
         <x-input name="question" label="Question" :value="$faq->question" required maxlength="160" placeholder="e.g. Can the e-Generator be charged with solar?" />
         <div class="t-field">
            <label for="f-answer" class="t-label">Answer <span class="req">*</span></label>
            <textarea id="f-answer" name="answer" rows="7" maxlength="1200" required class="t-textarea @error('answer') is-invalid @enderror">{{ old('answer', $faq->answer) }}</textarea>
            @error('answer')<span class="t-error">{{ $message }}</span>@else<span class="t-help">Plain text, up to 1,200 characters.</span>@enderror
         </div>
      </div>
   </section>

   <section class="t-card">
      <div class="t-card__head"><h2>Publishing</h2></div>
      <div class="t-card__body">
         <div class="t-field">
            <label for="f-page" class="t-label">Shown on <span class="req">*</span></label>
            <select id="f-page" name="page" class="t-select">
               @foreach (config('thor.faq_pages') as $key => $label)
                  <option value="{{ $key }}" @selected(old('page', $faq->page) === $key)>{{ $label }}</option>
               @endforeach
            </select>
         </div>
         <div style="margin-top:18px">
            <label class="t-switch">
               <input type="hidden" name="is_active" value="0">
               <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $faq->is_active))>
               <span class="t-switch__track"></span>
               Show on website
            </label>
         </div>
         <div style="margin-top:18px">
            <x-input name="sort_order" type="number" label="Display order" :value="$faq->sort_order" required min="0" help="Lower numbers show first. The first question opens by default." />
         </div>
      </div>
      <div class="t-card__foot">
         <a href="{{ route('admin.faqs.index') }}" class="t-btn t-btn--ghost">Cancel</a>
         <button type="submit" class="t-btn">{{ $faq->exists ? 'Save changes' : 'Add question' }}</button>
      </div>
   </section>
</form>
@endsection
