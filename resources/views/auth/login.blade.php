@extends('layouts.auth')
@section('title', 'Sign in')
@section('content')
    <span class="t-kicker"><img src="{{ asset('assets/images/brand/thunder-icon.svg') }}" alt=""> Welcome back</span>
    <h1>Sign in to your account</h1>
    <p>Enter your email and password to manage the "ThOR" BESS website.</p>

    @if (session('status'))
        <div class="t-alert t-alert--success" style="margin-bottom:20px"><x-icon name="check-circle" /><span>{{ session('status') }}</span></div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" novalidate>
        @csrf
        <x-input name="email" type="email" label="Email" icon="mail" placeholder="you@thorenergybox.com" autocomplete="username" autofocus required />
        <x-input name="password" type="password" label="Password" icon="lock" placeholder="Enter your password" autocomplete="current-password" required />

        <div class="t-auth__row">
            <label class="t-check"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Keep me signed in</label>
            <a href="{{ route('password.request') }}" class="t-link">Forgot password?</a>
        </div>

        <button type="submit" class="t-btn t-btn--block">Sign in <x-icon name="arrow-right" /></button>
    </form>

    @if (config('thor.allow_registration'))
        <p class="t-auth__alt">Don’t have an account? <a href="{{ route('register') }}" class="t-link">Create one</a></p>
    @endif
@endsection
