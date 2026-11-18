@extends('layouts.app')
@section('title', 'Users')
@section('content')
<x-page-head title="Users" kicker="Team access" description="People who can sign in to this admin panel.">
   <a href="{{ route('admin.users.create') }}" class="t-btn"><x-icon name="plus" /> Add user</a>
</x-page-head>

<section class="t-card">
   <div class="t-card__head">
      <form method="GET" class="t-filters" style="width:100%">
         <div class="t-input-wrap">
            <x-icon name="search" />
            <input type="search" name="q" value="{{ $q }}" placeholder="Search name or email…" class="t-input t-input--icon">
         </div>
         <button type="submit" class="t-btn t-btn--navy">Search</button>
         @if ($q)<a href="{{ route('admin.users.index') }}" class="t-btn t-btn--ghost">Clear</a>@endif
      </form>
   </div>
   <div class="t-table-wrap">
      <table class="t-table">
         <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Last sign-in</th><th class="t-right">Actions</th></tr></thead>
         <tbody>
            @forelse ($users as $user)
               <tr>
                  <td>
                     <div class="t-flex">
                        <span class="t-avatar t-avatar--sm">{{ $user->initials() }}</span>
                        <span>
                           <a href="{{ route('admin.users.edit', $user) }}" class="t-row-link">{{ $user->name }}</a>
                           @if ($user->is(auth()->user()))<span class="t-badge t-badge--neutral t-badge--plain" style="margin-left:6px">You</span>@endif
                           <span class="t-sub">{{ $user->email }}</span>
                        </span>
                     </div>
                  </td>
                  <td><span class="t-badge {{ $user->isAdmin() ? 't-badge--navy' : 't-badge--neutral' }} t-badge--plain">{{ $user->roleLabel() }}</span></td>
                  <td><span class="t-badge {{ $user->is_active ? 't-badge--success' : 't-badge--error' }}">{{ $user->is_active ? 'Active' : 'Deactivated' }}</span></td>
                  <td class="t-nowrap">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                  <td class="t-right t-nowrap" x-data>
                     <a href="{{ route('admin.users.edit', $user) }}" class="t-icon-action" title="Edit"><x-icon name="pencil" /></a>
                     @unless ($user->is(auth()->user()))
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display:inline" @submit="if (!confirm('Delete {{ addslashes($user->name) }}? They will lose access immediately.')) $event.preventDefault()">
                           @csrf @method('DELETE')
                           <button type="submit" class="t-icon-action t-icon-action--danger" title="Delete"><x-icon name="trash" /></button>
                        </form>
                     @endunless
                  </td>
               </tr>
            @empty
               <tr><td colspan="5"><div class="t-empty"><h3>No users found</h3></div></td></tr>
            @endforelse
         </tbody>
      </table>
   </div>
   @if ($users->hasPages())
      <div class="t-card__foot">{{ $users->links() }}</div>
   @endif
</section>
@endsection
