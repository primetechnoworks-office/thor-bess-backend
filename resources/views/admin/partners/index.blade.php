@extends('layouts.app')
@section('title', 'Partners')
@section('content')
<x-page-head title="Partners" kicker="Logos & incubators" description="The logo slider on the home page and the incubators list on the About page.">
   <a href="{{ route('admin.partners.create') }}" class="t-btn"><x-icon name="plus" /> Add partner logo</a>
</x-page-head>

<div class="t-grid t-grid--main">
   <section class="t-card">
      <div class="t-card__head"><div><h2>Partner logos</h2><p>Home page slider</p></div></div>
      @if ($partners->isEmpty())
         <div class="t-empty"><h3>No partner logos</h3><p>The slider is hidden until at least one logo is live.</p></div>
      @else
         <div class="t-table-wrap">
            <table class="t-table">
               <thead><tr><th>Logo</th><th>Link</th><th>Website</th><th>Order</th><th class="t-right">Actions</th></tr></thead>
               <tbody>
                  @foreach ($partners as $p)
                     <tr>
                        <td style="min-width:260px">
                           <div class="t-flex">
                              <img src="{{ \App\Support\Media::forAdmin($p->logo) }}" alt="" class="t-thumb" style="object-fit:contain;background:#fff;padding:4px" loading="lazy" onerror="this.style.visibility='hidden'">
                              <a href="{{ route('admin.partners.edit', $p) }}" class="t-row-link">{{ $p->name }}</a>
                           </div>
                        </td>
                        <td>@if ($p->url)<a href="{{ $p->url }}" target="_blank" rel="noopener" class="t-link">Visit ↗</a>@else<span class="t-soft">—</span>@endif</td>
                        <td><span class="t-badge {{ $p->is_active ? 't-badge--success' : 't-badge--neutral' }}">{{ $p->is_active ? 'Live' : 'Hidden' }}</span></td>
                        <td class="t-soft">{{ $p->sort_order }}</td>
                        <td class="t-right t-nowrap" x-data>
                           <a href="{{ route('admin.partners.edit', $p) }}" class="t-icon-action" title="Edit"><x-icon name="pencil" /></a>
                           <form method="POST" action="{{ route('admin.partners.destroy', $p) }}" style="display:inline" @submit="if (!confirm('Delete the {{ addslashes($p->name) }} logo?')) $event.preventDefault()">
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

   <form method="POST" action="{{ route('admin.partners.incubators') }}" class="t-card">
      @csrf @method('PUT')
      <div class="t-card__head"><div><h2>Incubators</h2><p>About page list with check marks</p></div></div>
      <div class="t-card__body">
         <div class="t-field">
            <label for="f-incubators" class="t-label">Incubation &amp; association partners</label>
            <textarea id="f-incubators" name="incubators" rows="9" maxlength="1500" class="t-textarea @error('incubators') is-invalid @enderror">{{ old('incubators', implode("\n", $incubators)) }}</textarea>
            @error('incubators')<span class="t-error">{{ $message }}</span>@else<span class="t-help">One per line (up to 10, 120 characters each), e.g. “TiHAN, IIT Hyderabad — Defence &amp; MeitY”.</span>@enderror
         </div>
      </div>
      <div class="t-card__foot"><span></span><button type="submit" class="t-btn">Save list</button></div>
   </form>
</div>
@endsection
