@php($user = auth()->user())
<header class="t-header sticky top-0 z-999 flex w-full border-b">
   <div class="flex grow items-center justify-between gap-3 px-4 py-3 lg:px-6 lg:py-4">
      <div class="flex items-center gap-3">
         <button type="button" class="t-icon-btn t-icon-btn--square" @click.stop="sidebarToggle = !sidebarToggle" aria-label="Toggle sidebar">
            <x-icon name="panel" class="hidden lg:block" />
            <x-icon name="menu" x-show="!sidebarToggle" class="lg:hidden" />
            <x-icon name="x" x-show="sidebarToggle" x-cloak class="lg:hidden" />
         </button>
         <div class="hidden sm:block">
            <p class="t-header-title">@yield('title', 'Dashboard')</p>
            <span class="t-header-sub">{{ now()->format('l, d M Y') }}</span>
         </div>
      </div>

      <div class="flex items-center gap-2 sm:gap-3">
         <a href="{{ route('admin.leads.index', ['status' => 'new']) }}" class="t-icon-btn relative" title="New leads">
            <x-icon name="inbox" />
            @if (($newLeadCount = \App\Models\Lead::where('status', 'new')->count()) > 0)
               <span class="absolute top-0.5 right-0 z-1 h-2.5 w-2.5 rounded-full" style="background:var(--t-green);box-shadow:0 0 0 2px var(--t-surface)"></span>
            @endif
         </a>
         <button type="button" class="t-icon-btn" @click.prevent="darkMode = !darkMode" aria-label="Toggle dark mode">
            <x-icon name="moon" x-show="!darkMode" />
            <x-icon name="sun" x-show="darkMode" x-cloak />
         </button>

         <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
            <button type="button" class="t-user-btn" @click="open = !open" :aria-expanded="open">
               <span class="t-avatar">{{ $user->initials() }}</span>
               <span class="hidden text-left md:block">
                  <span class="block text-sm font-semibold">{{ $user->name }}</span>
                  <span class="block text-xs t-soft">{{ $user->roleLabel() }}</span>
               </span>
               <x-icon name="chevron-down" ::class="open && 'rotate-180'" />
            </button>
            <div x-show="open" x-cloak x-transition.origin.top.right class="t-menu">
               <div class="t-menu__head">
                  <strong>{{ $user->name }}</strong>
                  <span>{{ $user->email }}</span>
               </div>
               <a href="{{ route('admin.profile.edit') }}" class="t-menu__item"><x-icon name="user" /> Edit profile</a>
               <a href="{{ route('admin.profile.edit') }}#password" class="t-menu__item"><x-icon name="lock" /> Change password</a>
               <a href="{{ config('thor.frontend_url') }}" target="_blank" rel="noopener" class="t-menu__item"><x-icon name="globe" /> View website</a>
               <form method="POST" action="{{ route('admin.logout') }}" style="margin-top:6px;padding-top:6px;border-top:1px solid var(--t-line)">
                  @csrf
                  <button type="submit" class="t-menu__item t-menu__item--danger"><x-icon name="logout" /> Sign out</button>
               </form>
            </div>
         </div>
      </div>
   </div>
   <span class="t-header__progress"></span>
</header>
