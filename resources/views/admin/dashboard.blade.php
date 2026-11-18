@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
@php
   $types = config('thor.lead_types');
   $typeTotal = max(1, $byType->sum());
   $typeTone = ['contact' => '', 'datasheet' => 'navy', 'quote' => 'bolt'];
   $hour = now()->hour;
   $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
@endphp
<div class="t-stack">
   <section class="t-hero">
      <img class="t-hero__bolt" src="{{ asset('assets/images/brand/thunder-icon.svg') }}" alt="">
      <span class="t-kicker"><img src="{{ asset('assets/images/brand/thunder-icon.svg') }}" alt=""> {{ $greeting }}</span>
      <h2>{{ auth()->user()->name }}, you have <span>{{ $stats['new'] }} new {{ Str::plural('lead', $stats['new']) }}</span> waiting</h2>
      <p>Enquiries, datasheet requests and e-Generator quotes from the website land here the moment they are submitted.</p>
      <div class="t-actions">
         <a href="{{ route('admin.leads.index', ['status' => 'new']) }}" class="t-btn">Review new leads <x-icon name="arrow-right" /></a>
         <a href="{{ route('admin.posts.create') }}" class="t-btn t-btn--light"><x-icon name="plus" /> Write a post</a>
      </div>
   </section>

   <div class="t-grid t-grid--stats">
      <div class="t-stat">
         <span class="t-stat__icon t-stat__icon--green"><x-icon name="inbox" /></span>
         <span class="t-stat__label">Total leads</span>
         <div class="t-stat__value">{{ number_format($stats['total']) }}</div>
      </div>
      <div class="t-stat">
         <span class="t-stat__icon t-stat__icon--bolt"><x-icon name="zap" /></span>
         <span class="t-stat__label">Awaiting reply</span>
         <div class="t-stat__value">{{ number_format($stats['new']) }}</div>
      </div>
      <div class="t-stat">
         <span class="t-stat__icon t-stat__icon--sky"><x-icon name="trending-up" /></span>
         <span class="t-stat__label">Leads this month</span>
         <div class="t-stat__value">
            {{ number_format($stats['month']) }}
            @if (! is_null($stats['month_change']))
               <span class="t-delta {{ $stats['month_change'] >= 0 ? 't-delta--up' : 't-delta--down' }}">{{ $stats['month_change'] >= 0 ? '↑' : '↓' }} {{ abs($stats['month_change']) }}%</span>
            @endif
         </div>
      </div>
      <div class="t-stat">
         <span class="t-stat__icon"><x-icon name="file-text" /></span>
         <span class="t-stat__label">Published posts</span>
         <div class="t-stat__value">{{ $stats['posts'] }} <span class="t-badge t-badge--neutral t-badge--plain">{{ $stats['drafts'] }} {{ Str::plural('draft', $stats['drafts']) }}</span></div>
      </div>
   </div>

   <div class="t-grid t-grid--main">
      <section class="t-card">
         <div class="t-card__head">
            <div><h2>Leads — last 14 days</h2><p>Every form submission from the website</p></div>
            <span class="t-badge t-badge--success t-badge--plain">{{ $trend->sum('total') }} total</span>
         </div>
         <div class="t-card__body">
            <div class="t-chart">
               @foreach ($trend as $i => $day)
                  <div class="t-chart__col">
                     <div class="t-chart__track">
                        <div class="t-chart__bar {{ $day['total'] ? '' : 'is-zero' }}" style="height: {{ max(2, $day['total'] / $trendMax * 100) }}%; animation-delay: {{ $i * 40 }}ms">
                           <span>{{ $day['label'] }} · {{ $day['total'] }} {{ Str::plural('lead', $day['total']) }}</span>
                        </div>
                     </div>
                     <span class="t-chart__label">{{ $day['short'] }}</span>
                  </div>
               @endforeach
            </div>
         </div>
      </section>

      <section class="t-card">
         <div class="t-card__head"><div><h2>Leads by form</h2><p>All-time split</p></div></div>
         <div class="t-card__body">
            <ul class="t-breakdown">
               @foreach ($types as $key => $label)
                  @php($count = $byType->get($key, 0))
                  <li>
                     <div class="t-breakdown__row"><span>{{ $label }}</span><span>{{ $count }}</span></div>
                     <div class="t-progress"><i class="{{ $typeTone[$key] ?? '' }}" style="width: {{ $count / $typeTotal * 100 }}%"></i></div>
                  </li>
               @endforeach
            </ul>
            <hr class="t-divider">
            <div class="t-filters">
               @foreach (config('thor.lead_statuses') as $key => $label)
                  <a href="{{ route('admin.leads.index', ['status' => $key]) }}" class="t-badge t-badge--{{ (new \App\Models\Lead(['status' => $key]))->statusTone() }}">{{ $label }} · {{ $byStatus->get($key, 0) }}</a>
               @endforeach
            </div>
         </div>
      </section>
   </div>

   <div class="t-grid t-grid--main">
      <section class="t-card">
         <div class="t-card__head">
            <div><h2>Recent leads</h2><p>Latest website submissions</p></div>
            <a href="{{ route('admin.leads.index') }}" class="t-btn t-btn--outline t-btn--sm">View all</a>
         </div>
         @if ($recentLeads->isEmpty())
            <div class="t-empty">
               <span class="t-empty__icon"><x-icon name="inbox" /></span>
               <h3>No leads yet</h3>
               <p>Once the website forms post to <code>/api/leads/*</code>, submissions will appear here.</p>
            </div>
         @else
            <div class="t-table-wrap">
               <table class="t-table">
                  <thead><tr><th>Contact</th><th>Form</th><th>Status</th><th class="t-right">Received</th></tr></thead>
                  <tbody>
                     @foreach ($recentLeads as $lead)
                        <tr>
                           <td>
                              <a href="{{ route('admin.leads.show', $lead) }}" class="t-row-link">{{ $lead->name }}</a>
                              <span class="t-sub">{{ $lead->company ?: $lead->email }}</span>
                           </td>
                           <td><span class="t-badge t-badge--navy t-badge--plain">{{ $lead->typeLabel() }}</span></td>
                           <td><span class="t-badge t-badge--{{ $lead->statusTone() }}">{{ $lead->statusLabel() }}</span></td>
                           <td class="t-right t-nowrap t-soft">{{ $lead->created_at->diffForHumans() }}</td>
                        </tr>
                     @endforeach
                  </tbody>
               </table>
            </div>
         @endif
      </section>

      <section class="t-card">
         <div class="t-card__head">
            <div><h2>Recently edited posts</h2></div>
            <a href="{{ route('admin.posts.index') }}" class="t-btn t-btn--outline t-btn--sm">All posts</a>
         </div>
         <div class="t-card__body" style="display:grid;gap:16px">
            @forelse ($recentPosts as $post)
               <a href="{{ route('admin.posts.edit', $post) }}" class="t-flex" style="align-items:flex-start">
                  @if ($post->imageUrl())
                     <img src="{{ $post->imageUrl() }}" alt="" class="t-thumb" loading="lazy" onerror="this.style.visibility='hidden'">
                  @else
                     <span class="t-thumb" style="display:grid;place-items:center"><x-icon name="image" size="18" /></span>
                  @endif
                  <span style="min-width:0">
                     <span class="t-row-link" style="display:block;font-size:14px;line-height:1.35">{{ Str::limit($post->title, 70) }}</span>
                     <span class="t-soft" style="font-size:12.5px">{{ $post->category }} · {{ $post->isLive() ? 'Live' : ucfirst($post->status) }}</span>
                  </span>
               </a>
            @empty
               <p class="t-muted">No posts yet.</p>
            @endforelse
         </div>
      </section>
   </div>
</div>
@endsection
