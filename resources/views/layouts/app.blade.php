<!doctype html>
<html lang="en">
<head>
   <meta charset="UTF-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <meta name="csrf-token" content="{{ csrf_token() }}">
   <meta name="robots" content="noindex, nofollow">
   <title>@yield('title', 'Dashboard') · "ThOR" BESS Admin</title>
   <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
   <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
   @stack('head') {{-- page/vendor CSS first, so the brand layer below can restyle it --}}
   <link href="{{ asset('assets/css/thor-admin.css') }}?v={{ filemtime(public_path('assets/css/thor-admin.css')) }}" rel="stylesheet">
</head>
<body
   x-data="{ loaded: true, darkMode: false, sidebarToggle: false }"
   x-init="darkMode = JSON.parse(localStorage.getItem('darkMode')) === true; $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
   :class="{ 'dark': darkMode === true }"
>
   @include('partials.preloader')
   <div class="flex h-screen overflow-hidden">
      @include('partials.sidebar')
      <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">
         {{-- Small-screen overlay behind the open sidebar --}}
         <div @click="sidebarToggle = false" :class="sidebarToggle ? 'block lg:hidden' : 'hidden'" class="fixed w-full h-screen z-9 bg-gray-900/50"></div>
         @include('partials.header')
         <main class="flex-1">
            <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
               @include('partials.flash')
               @yield('content')
            </div>
         </main>
         @include('partials.footer')
      </div>
   </div>
   <script src="{{ asset('assets/js/bundle.js') }}"></script>
   <script src="{{ asset('assets/vendor/parsley/jquery.min.js') }}"></script>
   <script src="{{ asset('assets/vendor/parsley/parsley.min.js') }}"></script>
   <script src="{{ asset('assets/js/thor-validation.js') }}?v={{ filemtime(public_path('assets/js/thor-validation.js')) }}"></script>
   @stack('scripts')
</body>
</html>
