@extends('layouts.app')
@section('title', 'Hero & banners')
@section('content')
@php
   use App\Support\Media;
   $youtube = old('youtube', $hero['youtube_id'] ?? '');
   $video = Media::forAdmin($hero['video'] ?? null);
@endphp
<x-page-head title="Hero &amp; banners" kicker="Page visuals" description="The film behind the home page heading and the banner photo at the top of each inner page." />

<form method="POST" enctype="multipart/form-data" action="{{ route('admin.media.update') }}" class="t-stack">
   @csrf @method('PUT')

   <div class="t-grid t-grid--main">
      <section class="t-card">
         <div class="t-card__head"><div><h2>Home page film</h2><p>Plays muted and looped behind the home page heading.</p></div></div>
         <div class="t-card__body">
            <x-input name="youtube" label="YouTube video" :value="$youtube" maxlength="255" icon="external" data-parsley-youtube
                     placeholder="https://www.youtube.com/watch?v=…"
                     help="Paste the video's link (or its 11-character ID). Leave empty to show only the poster image." />
            @if ($youtube && ! $errors->has('youtube'))
               <p class="t-help" style="margin-top:8px">Currently: <a class="t-link" href="https://www.youtube.com/watch?v={{ $hero['youtube_id'] ?? '' }}" target="_blank" rel="noopener">youtube.com/watch?v={{ $hero['youtube_id'] ?? '' }} ↗</a></p>
            @endif
            <hr class="t-divider">
            <div class="t-field">
               <label for="f-video" class="t-label">Or upload an MP4 film</label>
               @if ($video)
                  <video src="{{ $video }}" muted loop playsinline controls preload="metadata" style="width:100%;max-height:220px;border-radius:10px;background:#000"></video>
               @endif
               <input id="f-video" type="file" name="video_file" accept="video/mp4" data-parsley-max-file-size="25" data-parsley-file-types="mp4" data-parsley-file-types-message="The film must be an MP4 video." class="t-input @error('video_file') is-invalid @enderror" style="padding:8px 10px;margin-top:10px">
               @error('video_file')<span class="t-error">{{ $message }}</span>@else<span class="t-help">Optional. Starts faster than YouTube and is used instead of it. Ideal: 1920×1080, 10–20 s loop, under 6 MB (max 25 MB).</span>@enderror
               @if ($video)
                  <label class="t-check" style="margin-top:10px"><input type="checkbox" name="remove_video" value="1"> Remove uploaded film (go back to YouTube)</label>
               @endif
            </div>
         </div>
      </section>

      <section class="t-card" x-data="{ preview: @js(Media::forAdmin($hero['poster'] ?? null)) }">
         <div class="t-card__head"><div><h2>Poster image</h2><p>Shown until the film starts, and instead of it for visitors who turn off motion.</p></div></div>
         <div class="t-card__body">
            <div class="t-photo t-photo--wide"><template x-if="preview"><img :src="preview" alt=""></template></div>
            <div class="t-field" style="margin-top:16px">
               <label for="f-poster" class="t-label">Replace poster</label>
               <input id="f-poster" type="file" name="poster_file" accept="image/jpeg,image/png,image/webp" data-parsley-max-file-size="5" data-parsley-file-types="jpg,jpeg,png,webp" data-parsley-file-types-message="Please choose a JPG, PNG or WebP image." class="t-input @error('poster_file') is-invalid @enderror" style="padding:8px 10px"
                      @change="const f = $event.target.files[0]; if (f) preview = URL.createObjectURL(f)">
               @error('poster_file')<span class="t-error">{{ $message }}</span>@else<span class="t-help">Use a frame from the film (16:9, at least 1920 px wide).</span>@enderror
            </div>
         </div>
      </section>
   </div>

   <section class="t-card">
      <div class="t-card__head"><div><h2>Page banners</h2><p>Photo behind the title at the top of each page. Headings stay as designed.</p></div></div>
      <div class="t-card__body">
         @foreach (config('thor.banner_pages') as $key => $label)
            <div class="t-media-row" x-data="{ preview: @js(Media::forAdmin($banners[$key] ?? null)) }">
               <div class="t-photo"><template x-if="preview"><img :src="preview" alt=""></template></div>
               <div class="t-field">
                  <label for="f-banner-{{ $key }}" class="t-label">{{ $label }}</label>
                  <input id="f-banner-{{ $key }}" type="file" name="banner_files[{{ $key }}]" accept="image/jpeg,image/png,image/webp" data-parsley-max-file-size="5" data-parsley-file-types="jpg,jpeg,png,webp" data-parsley-file-types-message="Please choose a JPG, PNG or WebP image."
                         class="t-input @error('banner_files.'.$key) is-invalid @enderror" style="padding:8px 10px"
                         @change="const f = $event.target.files[0]; if (f) preview = URL.createObjectURL(f)">
                  @error('banner_files.'.$key)<span class="t-error">{{ $message }}</span>@else<span class="t-help">Wide landscape photo, at least 1920 px wide, up to 5 MB. Text sits on the left, so keep the subject to the right.</span>@enderror
               </div>
            </div>
         @endforeach
      </div>
      <div class="t-card__foot">
         <span class="t-soft" style="font-size:13px">Changes appear on the website straight away.</span>
         <button type="submit" class="t-btn">Save hero &amp; banners</button>
      </div>
   </section>
</form>
@endsection
