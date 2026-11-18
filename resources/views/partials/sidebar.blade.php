@php
    $newLeads = \App\Models\Lead::where('status', 'new')->count();
    $nav = [
        ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
        ['label' => 'Leads', 'icon' => 'inbox', 'route' => 'admin.leads.index', 'active' => 'admin.leads.*', 'count' => $newLeads],
        ['label' => 'Blog posts', 'icon' => 'file-text', 'route' => 'admin.posts.index', 'active' => 'admin.posts.*'],
    ];
    $website = [
        ['label' => 'Products', 'icon' => 'battery', 'route' => 'admin.products.index', 'active' => 'admin.products.*'],
        ['label' => 'Testimonials', 'icon' => 'star', 'route' => 'admin.testimonials.index', 'active' => 'admin.testimonials.*'],
        ['label' => 'Team', 'icon' => 'users', 'route' => 'admin.team.index', 'active' => 'admin.team.*'],
        ['label' => 'Milestones', 'icon' => 'trending-up', 'route' => 'admin.milestones.index', 'active' => 'admin.milestones.*'],
        ['label' => 'FAQs', 'icon' => 'alert', 'route' => 'admin.faqs.index', 'active' => 'admin.faqs.*'],
        ['label' => 'Partners', 'icon' => 'globe', 'route' => 'admin.partners.index', 'active' => 'admin.partners.*'],
        ['label' => 'Hero & banners', 'icon' => 'image', 'route' => 'admin.media.edit', 'active' => 'admin.media.*'],
    ];
    $account = [
        ['label' => 'My profile', 'icon' => 'user', 'route' => 'admin.profile.edit', 'active' => 'admin.profile.*'],
    ];
    if (auth()->user()->isAdmin()) {
        $website[] = ['label' => 'Certificates & awards', 'icon' => 'star', 'route' => 'admin.credentials.edit', 'active' => 'admin.credentials.*'];
        $website[] = ['label' => 'Company settings', 'icon' => 'building', 'route' => 'admin.settings.edit', 'active' => 'admin.settings.*'];
        array_unshift($account, ['label' => 'Users', 'icon' => 'shield', 'route' => 'admin.users.index', 'active' => 'admin.users.*']);
    }
@endphp
<aside
   :class="sidebarToggle ? 'translate-x-0 lg:w-[90px] is-collapsed' : '-translate-x-full'"
   class="t-sidebar sidebar fixed left-0 top-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-hidden border-r px-5 lg:static lg:translate-x-0"
>
   <div :class="sidebarToggle ? 'lg:justify-center' : ''" class="flex items-center gap-2 pt-7 pb-8">
      <a href="{{ route('admin.dashboard') }}" class="t-brand">
         <img src="{{ asset('assets/images/brand/thor-mark.png') }}" alt='"ThOR" BESS'>
         <span :class="sidebarToggle ? 'lg:hidden' : ''">
            <span class="t-brand__name">"ThOR" <span>BESS</span></span>
            <span class="t-brand__sub">Admin panel</span>
         </span>
      </a>
   </div>

   <div class="flex flex-1 flex-col overflow-y-auto no-scrollbar">
      <nav>
         <h3 class="t-nav-title"><span :class="sidebarToggle ? 'lg:hidden' : ''">Manage</span></h3>
         <ul class="t-nav">
            @foreach ($nav as $item)
               <li>
                  <a href="{{ route($item['route']) }}" class="t-nav__link {{ request()->routeIs($item['active']) ? 'is-active' : '' }}" title="{{ $item['label'] }}">
                     <x-icon :name="$item['icon']" />
                     <span :class="sidebarToggle ? 'lg:hidden' : ''">{{ $item['label'] }}</span>
                     @if (! empty($item['count']))
                        <span class="t-nav__count">{{ $item['count'] > 99 ? '99+' : $item['count'] }}</span>
                     @endif
                  </a>
               </li>
            @endforeach
         </ul>

         <h3 class="t-nav-title"><span :class="sidebarToggle ? 'lg:hidden' : ''">Website</span></h3>
         <ul class="t-nav">
            @foreach ($website as $item)
               <li>
                  <a href="{{ route($item['route']) }}" class="t-nav__link {{ request()->routeIs($item['active']) ? 'is-active' : '' }}" title="{{ $item['label'] }}">
                     <x-icon :name="$item['icon']" />
                     <span :class="sidebarToggle ? 'lg:hidden' : ''">{{ $item['label'] }}</span>
                  </a>
               </li>
            @endforeach
         </ul>

         <h3 class="t-nav-title"><span :class="sidebarToggle ? 'lg:hidden' : ''">Account</span></h3>
         <ul class="t-nav">
            @foreach ($account as $item)
               <li>
                  <a href="{{ route($item['route']) }}" class="t-nav__link {{ request()->routeIs($item['active']) ? 'is-active' : '' }}" title="{{ $item['label'] }}">
                     <x-icon :name="$item['icon']" />
                     <span :class="sidebarToggle ? 'lg:hidden' : ''">{{ $item['label'] }}</span>
                  </a>
               </li>
            @endforeach
            <li>
               <form method="POST" action="{{ route('admin.logout') }}">
                  @csrf
                  <button type="submit" class="t-nav__link w-full" title="Sign out" style="border:0;background:none;cursor:pointer;font:inherit">
                     <x-icon name="logout" />
                     <span :class="sidebarToggle ? 'lg:hidden' : ''">Sign out</span>
                  </button>
               </form>
            </li>
         </ul>
      </nav>

      <div class="t-sidebar-card" :class="sidebarToggle ? 'lg:hidden' : ''">
         <strong>Website API</strong>
         <p>The website forms post leads to <code style="color:#fff">/api/leads/*</code>; blog posts are served from <code style="color:#fff">/api/posts</code>.</p>
         <a href="{{ config('thor.frontend_url') }}" target="_blank" rel="noopener">Open website <x-icon name="external" size="14" /></a>
      </div>
   </div>
</aside>
