@extends('layouts.app')
@section('title', 'Lead #'.$lead->id)
@section('content')
<x-page-head :title="$lead->name" :crumbs="['Leads' => route('admin.leads.index'), $lead->typeLabel() => route('admin.leads.index', ['type' => $lead->type]), '#'.$lead->id => null]">
   <a href="mailto:{{ $lead->email }}?subject={{ rawurlencode('Re: your enquiry to "ThOR" BESS') }}" class="t-btn"><x-icon name="mail" /> Reply by email</a>
   <a href="{{ route('admin.leads.index') }}" class="t-btn t-btn--outline"><x-icon name="arrow-left" /> Back</a>
</x-page-head>

<div class="t-grid t-grid--main">
   <div class="t-stack">
      <section class="t-card">
         <div class="t-card__head">
            <div>
               <h2>{{ $lead->typeLabel() }}</h2>
               <p>Received {{ $lead->created_at->format('d M Y, h:i A') }} · {{ $lead->created_at->diffForHumans() }}</p>
            </div>
            <span class="t-badge t-badge--{{ $lead->statusTone() }}">{{ $lead->statusLabel() }}</span>
         </div>
         <div class="t-card__body">
            <dl class="t-dl">
               @foreach ($lead->summaryRows() as $label => $value)
                  <dt>{{ $label }}</dt>
                  <dd>
                     @if ($label === 'Email')
                        <a href="mailto:{{ $value }}" class="t-link">{{ $value }}</a>
                     @elseif ($label === 'Attachment' && $lead->attachment_path)
                        <a href="{{ route('admin.leads.attachment', $lead) }}" class="t-link"><x-icon name="paperclip" size="15" style="vertical-align:-2px" /> {{ $value }}</a>
                     @else
                        <span class="t-pre">{{ $value }}</span>
                     @endif
                  </dd>
               @endforeach
            </dl>
         </div>
      </section>
   </div>

   <div class="t-stack">
      <section class="t-card">
         <div class="t-card__head"><h2>Follow-up</h2></div>
         <form method="POST" action="{{ route('admin.leads.update', $lead) }}" class="t-card__body">
            @csrf @method('PUT')
            <div class="t-field">
               <label class="t-label" for="status">Status</label>
               <select id="status" name="status" class="t-select">
                  @foreach (config('thor.lead_statuses') as $key => $label)
                     <option value="{{ $key }}" @selected(old('status', $lead->status) === $key)>{{ $label }}</option>
                  @endforeach
               </select>
            </div>
            <div class="t-field">
               <label class="t-label" for="assigned_to">Assigned to</label>
               <select id="assigned_to" name="assigned_to" class="t-select">
                  <option value="">Unassigned</option>
                  @foreach ($users as $u)
                     <option value="{{ $u->id }}" @selected((int) old('assigned_to', $lead->assigned_to) === $u->id)>{{ $u->name }}</option>
                  @endforeach
               </select>
            </div>
            <div class="t-field">
               <label class="t-label" for="notes">Internal notes</label>
               <textarea id="notes" name="notes" rows="5" maxlength="5000" class="t-textarea @error('notes') is-invalid @enderror" placeholder="Call summary, quotation sent, next steps…">{{ old('notes', $lead->notes) }}</textarea>
               @error('notes')<span class="t-error">{{ $message }}</span>@enderror
               <span class="t-help">Only visible to admin users.</span>
            </div>
            <div style="margin-top:20px"><button type="submit" class="t-btn t-btn--block">Save changes</button></div>
         </form>
      </section>

      <section class="t-card">
         <div class="t-card__head"><h2>Contact</h2></div>
         <div class="t-card__body">
            <ul class="t-contact-list">
               <li><span><x-icon name="mail" /></span><a href="mailto:{{ $lead->email }}" class="t-link">{{ $lead->email }}</a></li>
               <li><span><x-icon name="phone" /></span><a href="tel:{{ preg_replace('/[^0-9+]/', '', $lead->phone) }}" class="t-heading">{{ $lead->phone }}</a></li>
               @if ($lead->company)<li><span><x-icon name="building" /></span><span class="t-heading">{{ $lead->company }}</span></li>@endif
            </ul>
            <hr class="t-divider">
            <ul class="t-meta-list">
               <li><span>Lead ID</span><span>#{{ $lead->id }}</span></li>
               <li><span>First contacted</span><span>{{ $lead->contacted_at?->format('d M Y, h:i A') ?? '—' }}</span></li>
               <li><span>IP address</span><span>{{ $lead->ip_address ?? '—' }}</span></li>
               <li><span>Browser</span><span title="{{ $lead->user_agent }}">{{ Str::limit($lead->user_agent ?? '—', 40) }}</span></li>
            </ul>
         </div>
         <div class="t-card__foot" x-data>
            <form method="POST" action="{{ route('admin.leads.destroy', $lead) }}" @submit="if (!confirm('Delete this lead permanently? This cannot be undone.')) $event.preventDefault()">
               @csrf @method('DELETE')
               <button type="submit" class="t-btn t-btn--danger-outline t-btn--sm"><x-icon name="trash" /> Delete lead</button>
            </form>
         </div>
      </section>
   </div>
</div>
@endsection
