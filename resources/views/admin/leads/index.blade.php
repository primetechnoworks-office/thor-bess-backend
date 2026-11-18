@extends('layouts.app')
@section('title', 'Leads')
@section('content')
@php($activeType = $filters['type'] ?? null)
<x-page-head title="Leads" kicker="Website enquiries" description="Contact enquiries, datasheet requests and e-Generator quotes submitted on the website.">
   <a href="{{ route('admin.leads.export', request()->only(['type', 'status', 'q'])) }}" class="t-btn t-btn--outline"><x-icon name="download" /> Export CSV</a>
</x-page-head>

<div class="t-stack">
   <nav class="t-tabs" aria-label="Lead type">
      <a href="{{ route('admin.leads.index', array_filter(['status' => $filters['status'] ?? null, 'q' => $filters['q'] ?? null])) }}" class="{{ $activeType ? '' : 'is-active' }}">All <span>{{ $typeCounts->sum() }}</span></a>
      @foreach (config('thor.lead_types') as $key => $label)
         <a href="{{ route('admin.leads.index', array_filter(['type' => $key, 'status' => $filters['status'] ?? null, 'q' => $filters['q'] ?? null])) }}" class="{{ $activeType === $key ? 'is-active' : '' }}">{{ $label }} <span>{{ $typeCounts->get($key, 0) }}</span></a>
      @endforeach
   </nav>

   <section class="t-card">
      <div class="t-card__head">
         <form method="GET" class="t-filters" style="width:100%">
            @if ($activeType)<input type="hidden" name="type" value="{{ $activeType }}">@endif
            <div class="t-input-wrap">
               <x-icon name="search" />
               <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search name, company, email, phone…" class="t-input t-input--icon">
            </div>
            <select name="status" class="t-select" onchange="this.form.submit()" aria-label="Status">
               <option value="">All statuses</option>
               @foreach (config('thor.lead_statuses') as $key => $label)
                  <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
               @endforeach
            </select>
            <button type="submit" class="t-btn t-btn--navy">Filter</button>
            @if (array_filter($filters))
               <a href="{{ route('admin.leads.index') }}" class="t-btn t-btn--ghost">Clear</a>
            @endif
         </form>
      </div>

      @if ($leads->isEmpty())
         <div class="t-empty">
            <span class="t-empty__icon"><x-icon name="inbox" /></span>
            <h3>No leads found</h3>
            <p>{{ array_filter($filters) ? 'Try a different filter or search term.' : 'Submissions from the website forms will show up here.' }}</p>
         </div>
      @else
         <div class="t-table-wrap">
            <table class="t-table">
               <thead>
                  <tr><th>Contact</th><th>Form</th><th>Subject</th><th>Status</th><th>Received</th><th class="t-right">Actions</th></tr>
               </thead>
               <tbody>
                  @foreach ($leads as $lead)
                     <tr>
                        <td>
                           <a href="{{ route('admin.leads.show', $lead) }}" class="t-row-link">{{ $lead->name }}</a>
                           <span class="t-sub">{{ $lead->company ? $lead->company.' · ' : '' }}{{ $lead->email }}</span>
                        </td>
                        <td><span class="t-badge t-badge--navy t-badge--plain">{{ $lead->typeLabel() }}</span></td>
                        <td style="min-width:180px;max-width:280px">{{ Str::limit($lead->subject ?: ($lead->message ?: '—'), 60) }}</td>
                        <td>
                           <span class="t-badge t-badge--{{ $lead->statusTone() }}">{{ $lead->statusLabel() }}</span>
                           @if ($lead->assignee)<span class="t-sub">→ {{ $lead->assignee->name }}</span>@endif
                        </td>
                        <td class="t-nowrap">
                           {{ $lead->created_at->format('d M Y') }}
                           <span class="t-sub">{{ $lead->created_at->format('h:i A') }}</span>
                        </td>
                        <td class="t-right t-nowrap">
                           <a href="{{ route('admin.leads.show', $lead) }}" class="t-icon-action" title="View"><x-icon name="eye" /></a>
                           <a href="mailto:{{ $lead->email }}" class="t-icon-action" title="Email {{ $lead->name }}"><x-icon name="mail" /></a>
                        </td>
                     </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
         @if ($leads->hasPages())
            <div class="t-card__foot">{{ $leads->links() }}</div>
         @endif
      @endif
   </section>
</div>
@endsection
