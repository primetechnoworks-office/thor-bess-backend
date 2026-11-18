@extends('layouts.app')
@section('title', 'Testimonials')
@section('content')
<x-page-head title="Testimonials" kicker="Client feedback" description="Quotes in the “What our clients say” slider on the home and About pages.">
   <a href="{{ route('admin.testimonials.create') }}" class="t-btn"><x-icon name="plus" /> Add testimonial</a>
</x-page-head>

@if ($testimonials->contains(fn ($t) => str_contains(strtolower($t->quote), 'lorem ipsum') || $t->name === 'John Doe'))
   <div class="t-alert t-alert--info" style="margin-bottom:24px">
      <x-icon name="alert" />
      <span>Some testimonials are still <strong>placeholder text</strong> from the website design (“Lorem ipsum”, “John Doe”). Replace them with real client quotes, or untick “Show on website”.</span>
   </div>
@endif

<section class="t-card">
   @if ($testimonials->isEmpty())
      <div class="t-empty">
         <span class="t-empty__icon"><x-icon name="star" /></span>
         <h3>No testimonials yet</h3>
         <p>The slider is hidden on the website until at least one testimonial is live.</p>
      </div>
   @else
      <div class="t-table-wrap">
         <table class="t-table">
            <thead><tr><th>Client</th><th>Quote</th><th>Status</th><th>Order</th><th class="t-right">Actions</th></tr></thead>
            <tbody>
               @foreach ($testimonials as $t)
                  <tr>
                     <td>
                        <div class="t-flex">
                           @if ($t->avatar)
                              <img src="{{ \App\Support\Media::forAdmin($t->avatar) }}" alt="" class="t-avatar t-avatar--sm" style="object-fit:cover" onerror="this.style.visibility='hidden'">
                           @else
                              <span class="t-avatar t-avatar--sm">{{ mb_strtoupper(mb_substr($t->name, 0, 1)) }}</span>
                           @endif
                           <span>
                              <a href="{{ route('admin.testimonials.edit', $t) }}" class="t-row-link">{{ $t->name }}</a>
                              <span class="t-sub">{{ $t->role }}</span>
                           </span>
                        </div>
                     </td>
                     <td style="min-width:260px;max-width:460px">“{{ Str::limit($t->quote, 140) }}”</td>
                     <td><span class="t-badge {{ $t->is_active ? 't-badge--success' : 't-badge--neutral' }}">{{ $t->is_active ? 'Live' : 'Hidden' }}</span></td>
                     <td class="t-soft">{{ $t->sort_order }}</td>
                     <td class="t-right t-nowrap" x-data>
                        <a href="{{ route('admin.testimonials.edit', $t) }}" class="t-icon-action" title="Edit"><x-icon name="pencil" /></a>
                        <form method="POST" action="{{ route('admin.testimonials.destroy', $t) }}" style="display:inline" @submit="if (!confirm('Delete this testimonial from {{ addslashes($t->name) }}?')) $event.preventDefault()">
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
