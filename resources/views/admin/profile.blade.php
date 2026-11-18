@extends('layouts.app')
@section('title', 'My profile')
@section('content')
<x-page-head title="My profile" kicker="Account" description="Update your details and keep your account secure." />

<div class="t-stack">
   <section class="t-card">
      <div class="t-card__body t-flex" style="flex-wrap:wrap;gap:18px">
         <span class="t-avatar t-avatar--lg">{{ $user->initials() }}</span>
         <div style="flex:1;min-width:200px">
            <h2 class="t-heading" style="margin:0;font-size:20px;font-weight:700">{{ $user->name }}</h2>
            <p class="t-muted" style="margin:4px 0 0">{{ $user->email }}</p>
         </div>
         <div class="t-actions">
            <span class="t-badge t-badge--navy t-badge--plain">{{ $user->roleLabel() }}</span>
            <span class="t-badge t-badge--neutral t-badge--plain">Last sign-in {{ $user->last_login_at?->diffForHumans() ?? '—' }}</span>
         </div>
      </div>
   </section>

   <div class="t-grid t-grid--2" style="align-items:start">
      <form method="POST" action="{{ route('admin.profile.update') }}" class="t-card">
         @csrf @method('PUT')
         <div class="t-card__head"><div><h2>Personal information</h2><p>Your name and sign-in email</p></div></div>
         <div class="t-card__body">
            <x-input name="name" label="Full name" :value="$user->name" bag="profile" required maxlength="80" icon="user" />
            <x-input name="email" type="email" label="Email" :value="$user->email" bag="profile" required maxlength="120" icon="mail" />
            <x-input name="phone" label="Phone" :value="$user->phone" bag="profile" maxlength="20" icon="phone" placeholder="+91 98765 43210" />
         </div>
         <div class="t-card__foot"><span></span><button type="submit" class="t-btn">Save profile</button></div>
      </form>

      <form method="POST" action="{{ route('admin.profile.password') }}" class="t-card" id="password">
         @csrf @method('PUT')
         <div class="t-card__head"><div><h2>Change password</h2><p>Other devices will be signed out</p></div></div>
         <div class="t-card__body">
            <x-input name="current_password" type="password" label="Current password" bag="password" required icon="lock" autocomplete="current-password" />
            <x-input name="password" type="password" label="New password" bag="password" required icon="lock" autocomplete="new-password" help="At least 8 characters." />
            <x-input name="password_confirmation" type="password" label="Confirm new password" bag="password" required icon="lock" autocomplete="new-password" />
         </div>
         <div class="t-card__foot"><span></span><button type="submit" class="t-btn t-btn--navy">Update password</button></div>
      </form>
   </div>
</div>
@endsection
