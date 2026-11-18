@extends('layouts.app')
@section('title', 'Company settings')
@section('content')
@php
   $v = fn (string $key) => old($key, $s[$key] ?? '');
   $offices = collect(old('offices', $s['offices'] ?? []))->map(fn ($o) => [
      'label' => $o['label'] ?? '', 'lines' => is_array($o['lines'] ?? null) ? implode("\n", $o['lines']) : ($o['lines'] ?? ''),
      'map' => $o['map'] ?? '', 'directions' => $o['directions'] ?? '',
   ])->values();
   $socials = old('socials', $s['socials'] ?? []);
@endphp
<x-page-head title="Company settings" kicker="Website" description="Contact details and links shown in the website's header, footer, contact page and product pages." />

<form method="POST" action="{{ route('admin.settings.update') }}" class="t-grid t-grid--main">
   @csrf @method('PUT')

   <div class="t-stack">
      <section class="t-card">
         <div class="t-card__head"><div><h2>Contact</h2><p>Phone numbers become tap-to-call links on the website.</p></div></div>
         <div class="t-card__body">
            <div class="t-form-grid">
               <x-input name="phone" label="Main phone" :value="$v('phone')" required icon="phone" placeholder="+91 83497 74339" pattern="[0-9 +()\-]{7,20}" data-parsley-pattern-message="Use 7–20 digits, spaces, + ( ) or - only." />
               <x-input name="whatsapp" label="WhatsApp number" :value="$v('whatsapp')" icon="phone" placeholder="+91 88506 25798" pattern="[0-9 +()\-]{7,20}" data-parsley-pattern-message="Use 7–20 digits, spaces, + ( ) or - only." help="Used for the “Chat on WhatsApp” link." />
               <x-input name="email" type="email" label="General email" :value="$v('email')" required icon="mail" />
               <x-input name="sales_email" type="email" label="Sales email" :value="$v('sales_email')" required icon="mail" />
               <x-input name="landline" label="Landline" :value="$v('landline')" icon="phone" pattern="[0-9 +()\-]{7,20}" data-parsley-pattern-message="Use 7–20 digits, spaces, + ( ) or - only." />
               <x-input name="fax" label="Fax" :value="$v('fax')" pattern="[0-9 +()\-]{7,20}" data-parsley-pattern-message="Use 7–20 digits, spaces, + ( ) or - only." />
            </div>
            <div style="margin-top:18px">
               <x-input name="hours" label="Working hours" :value="$v('hours')" maxlength="120" placeholder="Mon – Sat, 9:30 AM – 6:30 PM IST" />
            </div>
            <div class="t-field">
               <label for="f-address" class="t-label">Short address <span class="req">*</span></label>
               <textarea id="f-address" name="address" rows="2" maxlength="300" required class="t-textarea @error('address') is-invalid @enderror">{{ is_array($v('address')) ? implode("\n", $v('address')) : $v('address') }}</textarea>
               @error('address')<span class="t-error">{{ $message }}</span>@else<span class="t-help">Shown in the footer and contact section. One line per row.</span>@enderror
            </div>
         </div>
      </section>

      <section class="t-card" x-data="{ offices: @js($offices), add() { this.offices.push({ label: '', lines: '', map: '', directions: '' }) } }">
         <div class="t-card__head">
            <div><h2>Offices</h2><p>Listed on the contact page. A map link shows an embedded Google Map.</p></div>
            <button type="button" class="t-btn t-btn--outline t-btn--sm" @click="add()"><x-icon name="plus" /> Add office</button>
         </div>
         <div class="t-card__body">
            @if ($errors->has('offices.*'))
               <div class="t-alert t-alert--error" style="margin-bottom:16px"><x-icon name="alert" /><span>{{ collect($errors->get('offices.*'))->flatten()->first() }}</span></div>
            @endif
            <template x-for="(office, i) in offices" :key="i">
               <div class="t-repeat">
                  <div class="t-between" style="margin-bottom:12px">
                     <strong class="t-heading" x-text="office.label || 'New office'"></strong>
                     <button type="button" class="t-btn t-btn--danger-outline t-btn--sm" @click="offices.splice(i, 1)"><x-icon name="trash" /> Remove</button>
                  </div>
                  <div class="t-form-grid">
                     <div class="t-field">
                        <label class="t-label" :for="`office-label-${i}`">Name <span class="req">*</span></label>
                        <input class="t-input" :id="`office-label-${i}`" :name="`offices[${i}][label]`" x-model="office.label" placeholder="Corporate Office" maxlength="60" required>
                     </div>
                     <div class="t-field">
                        <label class="t-label" :for="`office-dir-${i}`">Directions link</label>
                        <input class="t-input" type="url" :id="`office-dir-${i}`" :name="`offices[${i}][directions]`" x-model="office.directions" data-parsley-https-url placeholder="https://www.google.com/maps/search/?api=1&query=…">
                     </div>
                     <div class="t-field t-span-2">
                        <label class="t-label" :for="`office-lines-${i}`">Address <span class="req">*</span></label>
                        <textarea class="t-textarea" rows="3" :id="`office-lines-${i}`" :name="`offices[${i}][lines]`" x-model="office.lines" placeholder="One line per row" required></textarea>
                     </div>
                     <div class="t-field t-span-2">
                        <label class="t-label" :for="`office-map-${i}`">Google Maps embed link</label>
                        <input class="t-input" type="url" :id="`office-map-${i}`" :name="`offices[${i}][map]`" x-model="office.map" data-parsley-pattern="^https://(www\.|maps\.)?google\.[a-z.]+/maps.*" data-parsley-pattern-message="Use a Google Maps embed link (https://www.google.com/maps?…&amp;output=embed)." placeholder="https://www.google.com/maps?q=…&output=embed">
                        <span class="t-help">Optional. In Google Maps: Share → Embed a map, and copy the link inside <code>src="…"</code>.</span>
                     </div>
                  </div>
               </div>
            </template>
            <p class="t-muted" x-show="!offices.length" x-cloak>No offices listed.</p>
         </div>
      </section>
   </div>

   <div class="t-stack">
      <section class="t-card">
         <div class="t-card__head"><div><h2>Social media</h2><p>Leave empty to hide an icon.</p></div></div>
         <div class="t-card__body">
            @foreach (\App\Models\Setting::SOCIAL_NETWORKS as $key => $label)
               <x-input name="socials[{{ $key }}]" type="url" :label="$label" :value="$socials[$key] ?? ''" :id="'f-social-'.$key" placeholder="https://…" data-parsley-https-url />
            @endforeach
         </div>
      </section>

      <section class="t-card">
         <div class="t-card__head"><h2>Links</h2></div>
         <div class="t-card__body">
            <x-input name="book_now_url" type="url" label="“Book now” payment page" :value="$v('book_now_url')" data-parsley-https-url placeholder="https://u.payu.in/…" help="PayU link used by the Book Now buttons on battery pages." />
            <x-input name="indiamart_url" type="url" label="IndiaMART store" :value="$v('indiamart_url')" data-parsley-https-url placeholder="https://www.indiamart.com/…" />
            <x-input name="google_profile" type="url" label="Google Business profile" :value="$v('google_profile')" data-parsley-https-url />
            <x-input name="google_review" type="url" label="Google review link" :value="$v('google_review')" data-parsley-https-url help="Where the “Review us on Google” button goes." />
         </div>
         <div class="t-card__foot">
            <span class="t-soft" style="font-size:13px">Changes appear on the website straight away.</span>
            <button type="submit" class="t-btn">Save settings</button>
         </div>
      </section>
   </div>
</form>
@endsection
