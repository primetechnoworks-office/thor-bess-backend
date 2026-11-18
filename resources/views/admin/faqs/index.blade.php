@extends('layouts.app')
@section('title', 'FAQs')
@section('content')
<x-page-head title="FAQs" kicker="Questions & answers" description="Frequently asked questions shown on website pages.">
   <a href="{{ route('admin.faqs.create', array_filter(['page' => $page])) }}" class="t-btn"><x-icon name="plus" /> Add question</a>
</x-page-head>

<div class="t-stack">
   @if (count(config('thor.faq_pages')) > 1)
      <nav class="t-tabs" aria-label="Page">
         <a href="{{ route('admin.faqs.index') }}" class="{{ $page ? '' : 'is-active' }}">All pages</a>
         @foreach (config('thor.faq_pages') as $key => $label)
            <a href="{{ route('admin.faqs.index', ['page' => $key]) }}" class="{{ $page === $key ? 'is-active' : '' }}">{{ $label }}</a>
         @endforeach
      </nav>
   @endif

   <section class="t-card">
      @if ($faqs->isEmpty())
         <div class="t-empty">
            <span class="t-empty__icon"><x-icon name="alert" /></span>
            <h3>No questions yet</h3>
            <p>The FAQ section is hidden on the website until it has at least one live question.</p>
         </div>
      @else
         <div class="t-table-wrap">
            <table class="t-table">
               <thead><tr><th>Question</th><th>Page</th><th>Website</th><th>Order</th><th class="t-right">Actions</th></tr></thead>
               <tbody>
                  @foreach ($faqs as $f)
                     <tr>
                        <td style="min-width:300px;max-width:560px">
                           <a href="{{ route('admin.faqs.edit', $f) }}" class="t-row-link">{{ $f->question }}</a>
                           <span class="t-sub">{{ Str::limit($f->answer, 120) }}</span>
                        </td>
                        <td class="t-nowrap">{{ $f->pageLabel() }}</td>
                        <td><span class="t-badge {{ $f->is_active ? 't-badge--success' : 't-badge--neutral' }}">{{ $f->is_active ? 'Live' : 'Hidden' }}</span></td>
                        <td class="t-soft">{{ $f->sort_order }}</td>
                        <td class="t-right t-nowrap" x-data>
                           <a href="{{ route('admin.faqs.edit', $f) }}" class="t-icon-action" title="Edit"><x-icon name="pencil" /></a>
                           <form method="POST" action="{{ route('admin.faqs.destroy', $f) }}" style="display:inline" @submit="if (!confirm('Delete this question?')) $event.preventDefault()">
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
</div>
@endsection
