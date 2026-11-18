@extends('layouts.app')
@section('title', 'Team')
@section('content')
<x-page-head title="Leadership team" kicker="About page" description="People shown in the team section of the website's About page.">
   <a href="{{ route('admin.team.create') }}" class="t-btn"><x-icon name="plus" /> Add team member</a>
</x-page-head>

<section class="t-card">
   @if ($members->isEmpty())
      <div class="t-empty">
         <span class="t-empty__icon"><x-icon name="users" /></span>
         <h3>No team members yet</h3>
         <p>Add people to show them on the About page.</p>
      </div>
   @else
      <div class="t-table-wrap">
         <table class="t-table">
            <thead><tr><th>Name</th><th>Role</th><th>LinkedIn</th><th>Status</th><th>Order</th><th class="t-right">Actions</th></tr></thead>
            <tbody>
               @foreach ($members as $m)
                  <tr>
                     <td>
                        <div class="t-flex">
                           @if ($m->image)
                              <img src="{{ \App\Support\Media::forAdmin($m->image) }}" alt="" class="t-avatar" style="object-fit:cover" onerror="this.style.visibility='hidden'">
                           @else
                              <span class="t-avatar" title="No photo yet — the website shows initials">{{ $m->initials() }}</span>
                           @endif
                           <a href="{{ route('admin.team.edit', $m) }}" class="t-row-link">{{ $m->name }}</a>
                        </div>
                     </td>
                     <td>{{ $m->role }}</td>
                     <td>
                        @if ($m->linkedin)
                           <a href="{{ $m->linkedin }}" target="_blank" rel="noopener" class="t-link">Profile ↗</a>
                        @else
                           <span class="t-soft">—</span>
                        @endif
                     </td>
                     <td><span class="t-badge {{ $m->is_active ? 't-badge--success' : 't-badge--neutral' }}">{{ $m->is_active ? 'Live' : 'Hidden' }}</span></td>
                     <td class="t-soft">{{ $m->sort_order }}</td>
                     <td class="t-right t-nowrap" x-data>
                        <a href="{{ route('admin.team.edit', $m) }}" class="t-icon-action" title="Edit"><x-icon name="pencil" /></a>
                        <form method="POST" action="{{ route('admin.team.destroy', $m) }}" style="display:inline" @submit="if (!confirm('Remove {{ addslashes($m->name) }} from the team page?')) $event.preventDefault()">
                           @csrf @method('DELETE')
                           <button type="submit" class="t-icon-action t-icon-action--danger" title="Remove"><x-icon name="trash" /></button>
                        </form>
                     </td>
                  </tr>
               @endforeach
            </tbody>
         </table>
      </div>
   @endif
</section>
@endsection
