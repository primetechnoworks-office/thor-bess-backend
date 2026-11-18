@extends('layouts.auth')
@section('title', 'Forgot password')
@section('content')
    <span class="t-kicker"><img src="{{ asset('assets/images/brand/thunder-icon.svg') }}" alt=""> Account recovery</span>
    <h1>Forgot your password?</h1>
    <p>Enter the email linked to your account and we’ll send you a link to set a new password.</p>

    @if (session('status'))
        <div class="t-alert t-alert--success" style="margin-bottom:20px"><x-icon name="check-circle" /><span>{{ session('status') }}</span></div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf
        <x-input name="email" type="email" label="Email" icon="mail" placeholder="you@thorenergybox.com" autocomplete="email" autofocus required />
        <div style="margin-top:24px">
            <button type="submit" class="t-btn t-btn--block">Email reset link <x-icon name="arrow-right" /></button>
        </div>
    </form>

    <p class="t-auth__alt"><a href="{{ route('login') }}" class="t-link">← Back to sign in</a></p>
@endsection
