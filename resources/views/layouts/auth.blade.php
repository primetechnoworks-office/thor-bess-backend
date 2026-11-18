<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') · "ThOR" BESS Admin</title>
    <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/thor-admin.css') }}?v={{ filemtime(public_path('assets/css/thor-admin.css')) }}" rel="stylesheet">
</head>
<body
    x-data="{ loaded: true, darkMode: false }"
    x-init="darkMode = JSON.parse(localStorage.getItem('darkMode')) === true; $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{ 'dark': darkMode === true }"
>
    @include('partials.preloader')
    <div class="t-auth">
        <div class="t-auth__form">
            <div class="t-auth__top">
                <a href="{{ route('login') }}" class="t-auth__logo">
                    <img src="{{ asset('assets/images/brand/thor-mark.png') }}" alt="">
                    <span><strong>"ThOR" <span>BESS</span></strong><small>Admin panel</small></span>
                </a>
                <button type="button" class="t-icon-btn" @click.prevent="darkMode = !darkMode" aria-label="Toggle dark mode">
                    <x-icon name="moon" x-show="!darkMode" />
                    <x-icon name="sun" x-show="darkMode" x-cloak />
                </button>
            </div>

            <div class="t-auth__body">
                @yield('content')
            </div>

            <p class="t-auth__foot">© {{ date('Y') }} "ThOR" BESS · SBB Energy. Authorised personnel only.</p>
        </div>

        <aside class="t-auth__panel" aria-hidden="true">
            <img class="t-auth__panel-img" src="{{ asset('assets/images/brand/container.png') }}" alt="">
            <img class="t-auth__panel-bolt" src="{{ asset('assets/images/brand/thunder-icon.svg') }}" alt="">
            <div class="t-auth__panel-copy">
                <span class="t-kicker"><img src="{{ asset('assets/images/brand/thunder-icon.svg') }}" alt=""> Clean energy, managed</span>
                <h2>Power the website behind <span>"ThOR" BESS</span></h2>
                <p>Track every enquiry, datasheet request and e-Generator quote, and keep the blog fresh — all from one place.</p>
                <ul class="t-auth__points">
                    <li><x-icon name="inbox" /> Lead management</li>
                    <li><x-icon name="file-text" /> Blog publishing</li>
                    <li><x-icon name="shield" /> Role-based access</li>
                </ul>
            </div>
        </aside>
    </div>
    <script defer src="{{ asset('assets/js/bundle.js') }}"></script>
    <script defer src="{{ asset('assets/vendor/parsley/jquery.min.js') }}"></script>
    <script defer src="{{ asset('assets/vendor/parsley/parsley.min.js') }}"></script>
    <script defer src="{{ asset('assets/js/thor-validation.js') }}?v={{ filemtime(public_path('assets/js/thor-validation.js')) }}"></script>
</body>
</html>
