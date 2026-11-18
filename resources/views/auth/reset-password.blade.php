@extends('layouts.auth')
@section('title', 'Reset password')
@section('content')
    <span class="t-kicker"><img src="{{ asset('assets/images/brand/thunder-icon.svg') }}" alt=""> Account recovery</span>
    <h1>Set a new password</h1>
    <p>Choose a strong password you don’t use anywhere else.</p>

    <form method="POST" action="{{ route('password.store') }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <x-input name="email" type="email" label="Email" icon="mail" :value="$request->email" autocomplete="username" required />
        <x-input name="password" type="password" label="New password" icon="lock" placeholder="At least 8 characters" autocomplete="new-password" autofocus required />
        <x-input name="password_confirmation" type="password" label="Confirm new password" icon="lock" placeholder="Repeat the new password" autocomplete="new-password" required />
        <div style="margin-top:24px">
            <button type="submit" class="t-btn t-btn--block">Reset password <x-icon name="arrow-right" /></button>
        </div>
    </form>

    <p class="t-auth__alt"><a href="{{ route('login') }}" class="t-link">← Back to sign in</a></p>
@endsection
