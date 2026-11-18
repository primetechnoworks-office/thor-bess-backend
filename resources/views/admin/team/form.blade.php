@extends('layouts.app')
@section('title', $member->exists ? 'Edit team member' : 'Add team member')
@section('content')
<x-page-head :title="$member->exists ? $member->name : 'Add team member'" :crumbs="['Team' => route('admin.team.index'), ($member->exists ? 'Edit' : 'New') => null]" />

<form method="POST" enctype="multipart/form-data" action="{{ $member->exists ? route('admin.team.update', $member) : route('admin.team.store') }}" class="t-grid t-grid--main">
   @csrf
   @if ($member->exists) @method('PUT') @endif

   <section class="t-card">
      <div class="t-card__head"><h2>Details</h2></div>
      <div class="t-card__body">
         <x-input name="name" label="Full name" :value="$member->name" required maxlength="80" placeholder="e.g. Karan Jani" />
         <x-input name="role" label="Role" :value="$member->role" required maxlength="120" placeholder="e.g. Director (ILT) – Sales & Marketing" />
         <x-input name="linkedin" type="url" label="LinkedIn profile" data-parsley-pattern="^https://([a-z]+\.)?linkedin\.com/.*" data-parsley-pattern-message="Please enter a LinkedIn profile link (https://www.linkedin.com/in/…)." :value="$member->linkedin" maxlength="255" placeholder="https://www.linkedin.com/in/…" help="Optional. Shown as a LinkedIn button on the team card." />
      </div>
   </section>

   <div class="t-stack">
      <x-photo-card name="photo" label="Portrait" :max-mb="2" :url="\App\Support\Media::forAdmin($member->image)" help="Portrait (3:4) works best. Without a photo the website shows the person's initials." />
      <section class="t-card">
         <div class="t-card__head"><h2>Publishing</h2></div>
         <div class="t-card__body">
            <label class="t-switch">
               <input type="hidden" name="is_active" value="0">
               <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $member->is_active))>
               <span class="t-switch__track"></span>
               Show on website
            </label>
            <div style="margin-top:18px">
               <x-input name="sort_order" type="number" label="Display order" :value="$member->sort_order" required min="0" help="Lower numbers show first." />
            </div>
         </div>
         <div class="t-card__foot">
            <a href="{{ route('admin.team.index') }}" class="t-btn t-btn--ghost">Cancel</a>
            <button type="submit" class="t-btn">{{ $member->exists ? 'Save changes' : 'Add team member' }}</button>
         </div>
      </section>
   </div>
</form>
@endsection
