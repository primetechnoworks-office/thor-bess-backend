@extends('layouts.app')
@section('title', 'Certificates & awards')
@section('content')
@php $v = fn (string $key) => old($key, $c[$key] ?? ''); @endphp
<x-page-head title="Certificates &amp; awards" kicker="Our Recognition" description="Registration numbers, patent, awards and the certificate shown in the website's “Our Recognition” section." />

<form method="POST" enctype="multipart/form-data" action="{{ route('admin.credentials.update') }}" class="t-grid t-grid--main">
   @csrf @method('PUT')

   <div class="t-stack">
      <section class="t-card">
         <div class="t-card__head"><div><h2>Registrations</h2><p>Shown as a list next to the certificate. Empty fields are hidden.</p></div></div>
         <div class="t-card__body">
            <div class="t-form-grid">
               <x-input name="dipp" label="DPIIT (Startup India) number" :value="$v('dipp')" required maxlength="40" placeholder="DIPP208347" />
               <x-input name="udyam" label="Udyam (MSME) number" :value="$v('udyam')" maxlength="40" />
               <x-input name="gem" label="GeM OEM seller ID" :value="$v('gem')" maxlength="40" />
               <x-input name="patent" label="IPR patent number" :value="$v('patent')" maxlength="40" help="Also quoted on the Technology page." />
            </div>
            <div style="margin-top:18px">
               <x-input name="dipp_verify" type="url" label="“Verify our certificate” link" :value="$v('dipp_verify')" maxlength="300" placeholder="https://www.startupindia.gov.in/…" />
            </div>
         </div>
      </section>

      <section class="t-card">
         <div class="t-card__head"><h2>Awards</h2></div>
         <div class="t-card__body">
            <div class="t-field">
               <label for="f-awards" class="t-label">Awards &amp; recognitions</label>
               <textarea id="f-awards" name="awards" rows="5" maxlength="2000" class="t-textarea @error('awards') is-invalid @enderror">{{ is_array($v('awards')) ? implode("\n", $v('awards')) : $v('awards') }}</textarea>
               @error('awards')<span class="t-error">{{ $message }}</span>@else<span class="t-help">One award per line — each gets a trophy icon on the website.</span>@enderror
            </div>
         </div>
         <div class="t-card__foot">
            <span class="t-soft" style="font-size:13px">Changes appear on the website straight away.</span>
            <button type="submit" class="t-btn">Save</button>
         </div>
      </section>
   </div>

   <section class="t-card" x-data="{ preview: @js(\App\Support\Media::forAdmin($c['certificate'] ?? null)) }">
      <div class="t-card__head"><h2>Certificate image</h2></div>
      <div class="t-card__body">
         <div class="t-photo t-photo--contain" style="aspect-ratio:1.414"><template x-if="preview"><img :src="preview" alt=""></template></div>
         <div class="t-field" style="margin-top:16px">
            <label for="f-cert" class="t-label">Replace certificate</label>
            <input id="f-cert" type="file" name="certificate_file" accept="image/jpeg,image/png,image/webp" class="t-input @error('certificate_file') is-invalid @enderror" style="padding:8px 10px"
                   @change="const f = $event.target.files[0]; if (f) preview = URL.createObjectURL(f)">
            @error('certificate_file')<span class="t-error">{{ $message }}</span>@else<span class="t-help">Landscape scan, at least 1600 px wide, up to 5 MB. Visitors can open it full-size.</span>@enderror
         </div>
      </div>
   </section>
</form>
@endsection
