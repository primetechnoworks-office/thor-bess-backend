@extends('layouts.auth')
@section('title', 'Create account')
@section('content')
    <span class="t-kicker"><img src="{{ asset('assets/images/brand/thunder-icon.svg') }}" alt=""> Join the team</span>
    <h1>Create your account</h1>
    <p>New accounts get editor access. An administrator can change your role.</p>

    <form method="POST" action="{{ route('register.store') }}" novalidate>
        @csrf
        <x-input name="name" label="Full name" icon="user" placeholder="Your name" autocomplete="name" autofocus required />
        <x-input name="email" type="email" label="Work email" icon="mail" placeholder="you@thorenergybox.com" autocomplete="email" required />
        <x-input name="password" type="password" label="Password" icon="lock" placeholder="At least 8 characters" autocomplete="new-password" required />
        <x-input name="password_confirmation" type="password" label="Confirm password" icon="lock" placeholder="Repeat your password" autocomplete="new-password" required />

        <div class="t-auth__row">
            <label class="t-check"><input type="checkbox" name="terms" value="1" @checked(old('terms'))> I agree to use this panel for "ThOR" BESS business only</label>
            @error('terms')<span class="t-error" style="margin-top:0">{{ $message }}</span>@enderror
        </div>

        <button type="submit" class="t-btn t-btn--block">Create account <x-icon name="arrow-right" /></button>
    </form>

    <p class="t-auth__alt">Already have an account? <a href="{{ route('login') }}" class="t-link">Sign in</a></p>
@endsection
