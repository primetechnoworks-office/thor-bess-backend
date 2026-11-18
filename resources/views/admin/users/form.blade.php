@extends('layouts.app')
@section('title', $user->exists ? 'Edit user' : 'Add user')
@section('content')
@php($isSelf = $user->exists && $user->is(auth()->user()))
<x-page-head :title="$user->exists ? $user->name : 'Add user'" :crumbs="['Users' => route('admin.users.index'), ($user->exists ? 'Edit' : 'New') => null]" />

<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="t-grid t-grid--main">
   @csrf
   @if ($user->exists) @method('PUT') @endif

   <section class="t-card">
      <div class="t-card__head"><div><h2>Account details</h2><p>Sign-in email and contact info</p></div></div>
      <div class="t-card__body">
         <div class="t-form-grid">
            <x-input name="name" label="Full name" :value="$user->name" required maxlength="80" />
            <x-input name="email" type="email" label="Email" :value="$user->email" required maxlength="120" autocomplete="off" />
            <x-input name="phone" label="Phone" :value="$user->phone" maxlength="20" placeholder="+91 98765 43210" pattern="[0-9 +()\-]{7,20}" data-parsley-pattern-message="Use 7–20 digits, spaces, + ( ) or - only." />
            <div class="t-field">
               <label for="f-role" class="t-label">Role <span class="req">*</span></label>
               <select id="f-role" name="role" class="t-select" @disabled($isSelf)>
                  @foreach (config('thor.roles') as $key => $label)
                     <option value="{{ $key }}" @selected(old('role', $user->role) === $key)>{{ $label }}</option>
                  @endforeach
               </select>
               @if ($isSelf)<input type="hidden" name="role" value="{{ $user->role }}">@endif
               <span class="t-help">Administrators can also manage users. Editors manage leads and posts.</span>
            </div>
         </div>
         <hr class="t-divider">
         <h3 class="t-heading" style="margin:0 0 4px;font-size:15px;font-weight:700">{{ $user->exists ? 'Reset password' : 'Password' }}</h3>
         <p class="t-muted" style="margin:0 0 16px;font-size:13.5px">{{ $user->exists ? 'Leave blank to keep the current password.' : 'Share it securely; they can change it from their profile.' }}</p>
         <div class="t-form-grid">
            <x-input name="password" type="password" label="Password" :required="! $user->exists" autocomplete="new-password" minlength="8" data-parsley-minlength-message="Use at least 8 characters." />
            <x-input name="password_confirmation" type="password" label="Confirm password" :required="! $user->exists" autocomplete="new-password" data-parsley-validate-if-empty="true" data-parsley-equalto="#f-password" data-parsley-equalto-message="The passwords don’t match." />
         </div>
      </div>
   </section>

   <section class="t-card">
      <div class="t-card__head"><h2>Access</h2></div>
      <div class="t-card__body">
         <label class="t-switch">
            <input type="hidden" name="is_active" value="{{ $isSelf ? 1 : 0 }}">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) @disabled($isSelf)>
            <span class="t-switch__track"></span>
            Account active
         </label>
         <span class="t-help">Deactivated users are signed out and can’t sign in.{{ $isSelf ? ' You can’t deactivate yourself.' : '' }}</span>
         @if ($user->exists)
            <hr class="t-divider">
            <ul class="t-meta-list">
               <li><span>Created</span><span>{{ $user->created_at->format('d M Y') }}</span></li>
               <li><span>Last sign-in</span><span>{{ $user->last_login_at?->format('d M Y, h:i A') ?? 'Never' }}</span></li>
            </ul>
         @endif
      </div>
      <div class="t-card__foot">
         <a href="{{ route('admin.users.index') }}" class="t-btn t-btn--ghost">Cancel</a>
         <button type="submit" class="t-btn">{{ $user->exists ? 'Save changes' : 'Create user' }}</button>
      </div>
   </section>
</form>
@endsection
