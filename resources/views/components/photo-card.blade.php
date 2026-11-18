@props(['name' => 'photo', 'label' => 'Photo', 'url' => null, 'help' => null, 'round' => false, 'contain' => false, 'removable' => true, 'maxMb' => 3, 'required' => false])
{{-- Single-image upload card with live preview and an optional "remove" option (posts its file as `name`, removal as `remove_{name}`). --}}
<section class="t-card" x-data="{ preview: @js($url), remove: false }">
   <div class="t-card__head"><h2>{{ $label }}</h2></div>
   <div class="t-card__body">
      <div @class(['t-photo', 't-photo--round' => $round, 't-photo--contain' => $contain])>
         <template x-if="preview && !remove"><img :src="preview" alt=""></template>
         <template x-if="!preview || remove"><span><x-icon name="image" size="28" /></span></template>
      </div>
      <div class="t-field" style="margin-top:16px">
         <label for="f-{{ $name }}" class="t-label">Upload</label>
         <input id="f-{{ $name }}" type="file" name="{{ $name }}" accept="image/jpeg,image/png,image/webp" class="t-input @error($name) is-invalid @enderror" style="padding:8px 10px"
                data-parsley-max-file-size="{{ $maxMb }}" data-parsley-file-types="jpg,jpeg,png,webp" data-parsley-file-types-message="Please choose a JPG, PNG or WebP image."
                @if ($required && ! $url) required data-parsley-required-message="Please upload an image." @endif
                @change="const f = $event.target.files[0]; if (f) { preview = URL.createObjectURL(f); remove = false }">
         @error($name)<span class="t-error">{{ $message }}</span>@else @if ($help)<span class="t-help">{{ $help }}</span>@endif @enderror
      </div>
      @if ($url && $removable)
         <label class="t-check" style="margin-top:12px"><input type="checkbox" name="remove_{{ $name }}" value="1" x-model="remove"> Remove current photo</label>
      @endif
   </div>
</section>
