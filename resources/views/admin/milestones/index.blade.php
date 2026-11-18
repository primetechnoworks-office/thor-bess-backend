@extends('layouts.app')
@section('title', 'Milestones')
@section('content')
<x-page-head title="Milestones" kicker="About page" description="Grants and work orders in the About page section “Backed by Grants. Proven in the Field.”">
   <a href="{{ route('admin.milestones.create') }}" class="t-btn"><x-icon name="plus" /> Add milestone</a>
</x-page-head>

<section class="t-card">
   @if ($milestones->isEmpty())
      <div class="t-empty">
         <span class="t-empty__icon"><x-icon name="trending-up" /></span>
         <h3>No milestones yet</h3>
         <p>Add grants and work orders to show them on the About page.</p>
      </div>
   @else
      <div class="t-table-wrap">
         <table class="t-table">
            <thead><tr><th>Milestone</th><th>Badge</th><th>Status</th><th>Website</th><th>Order</th><th class="t-right">Actions</th></tr></thead>
            <tbody>
               @foreach ($milestones as $m)
                  <tr>
                     <td style="min-width:280px">
                        <div class="t-flex">
                           @if ($m->image)
                              <img src="{{ \App\Support\Media::forAdmin($m->image) }}" alt="" class="t-thumb" loading="lazy" onerror="this.style.visibility='hidden'">
                           @else
                              <span class="t-thumb" style="display:grid;place-items:center"><x-icon name="image" size="18" /></span>
                           @endif
                           <span style="min-width:0">
                              <a href="{{ route('admin.milestones.edit', $m) }}" class="t-row-link">{{ $m->title }}</a>
                              <span class="t-sub">{{ $m->tag }}</span>
                           </span>
                        </div>
                     </td>
                     <td>{{ $m->badge ?: '—' }}</td>
                     <td>{{ $m->status ?: '—' }}</td>
                     <td><span class="t-badge {{ $m->is_active ? 't-badge--success' : 't-badge--neutral' }}">{{ $m->is_active ? 'Live' : 'Hidden' }}</span></td>
                     <td class="t-soft">{{ $m->sort_order }}</td>
                     <td class="t-right t-nowrap" x-data>
                        <a href="{{ route('admin.milestones.edit', $m) }}" class="t-icon-action" title="Edit"><x-icon name="pencil" /></a>
                        <form method="POST" action="{{ route('admin.milestones.destroy', $m) }}" style="display:inline" @submit="if (!confirm('Delete “{{ addslashes($m->title) }}”?')) $event.preventDefault()">
                           @csrf @method('DELETE')
                           <button type="submit" class="t-icon-action t-icon-action--danger" title="Delete"><x-icon name="trash" /></button>
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
