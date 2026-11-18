@extends('layouts.app')
@section('title', $post->exists ? 'Edit post' : 'New post')
@section('content')
<x-page-head :title="$post->exists ? 'Edit post' : 'New post'" :crumbs="['Blog posts' => route('admin.posts.index'), ($post->exists ? Str::limit($post->title, 40) : 'New') => null]">
   @if ($post->exists && $post->isLive())
      <a href="{{ config('thor.frontend_url') }}/blog/{{ $post->slug }}" target="_blank" rel="noopener" class="t-btn t-btn--outline"><x-icon name="external" /> View on website</a>
   @endif
</x-page-head>

<form method="POST" enctype="multipart/form-data" action="{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}"
      class="t-grid t-grid--main" x-data="{ preview: @js($post->imageUrl()), remove: false }">
   @csrf
   @if ($post->exists) @method('PUT') @endif

   <div class="t-stack">
      <section class="t-card">
         <div class="t-card__head"><h2>Content</h2></div>
         <div class="t-card__body">
            <x-input name="title" label="Title" :value="$post->title" required maxlength="200" placeholder="e.g. LFP vs Lead-Acid: Why LFP wins for backup power" />
            <x-input name="slug" label="URL slug" :value="$post->slug" maxlength="200" placeholder="auto-generated from the title" help="Lowercase letters, numbers and dashes. Leave empty to generate it from the title." />
            <div class="t-field">
               <label for="f-excerpt" class="t-label">Excerpt <span class="req">*</span></label>
               <textarea id="f-excerpt" name="excerpt" rows="3" maxlength="400" required class="t-textarea @error('excerpt') is-invalid @enderror" placeholder="One or two sentences shown on the blog cards.">{{ old('excerpt', $post->excerpt) }}</textarea>
               @error('excerpt')<span class="t-error">{{ $message }}</span>@enderror
            </div>
            <div class="t-field">
               <label for="f-body" class="t-label">Article body <span class="req">*</span></label>
               <textarea id="f-body" name="body" required class="t-textarea t-textarea--code @error('body') is-invalid @enderror">{{ old('body', $post->body) }}</textarea>
               @error('body')
                  <span class="t-error">{{ $message }}</span>
               @else
                  <span class="t-help">Formatting (same as the website): <code>## </code> heading · <code>### </code> sub-heading · <code>- </code> bullet · <code>&gt; </code> pull quote · <code>!! </code> highlight box · blank line = new paragraph.</span>
               @enderror
            </div>
         </div>
      </section>
   </div>

   <div class="t-stack">
      <section class="t-card">
         <div class="t-card__head"><h2>Publishing</h2></div>
         <div class="t-card__body">
            <div class="t-field">
               <label for="f-status" class="t-label">Status</label>
               <select id="f-status" name="status" class="t-select">
                  <option value="draft" @selected(old('status', $post->status) === 'draft')>Draft — hidden from the website</option>
                  <option value="published" @selected(old('status', $post->status) === 'published')>Published</option>
               </select>
            </div>
            <x-input name="published_at" type="date" label="Publish date" :value="$post->published_at?->format('Y-m-d')" help="A future date schedules the post." />
            <div class="t-field">
               <label for="f-category" class="t-label">Category <span class="req">*</span></label>
               <select id="f-category" name="category" class="t-select @error('category') is-invalid @enderror">
                  @foreach (config('thor.blog_categories') as $category)
                     <option value="{{ $category }}" @selected(old('category', $post->category) === $category)>{{ $category }}</option>
                  @endforeach
               </select>
               @error('category')<span class="t-error">{{ $message }}</span>@enderror
            </div>
            <x-input name="author" label="Author" :value="$post->author" required maxlength="80" />
            <div class="t-field">
               <label class="t-switch">
                  <input type="hidden" name="is_featured" value="0">
                  <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $post->is_featured))>
                  <span class="t-switch__track"></span>
                  Feature on the blog page
               </label>
            </div>
         </div>
         <div class="t-card__foot">
            <a href="{{ route('admin.posts.index') }}" class="t-btn t-btn--ghost">Cancel</a>
            <button type="submit" class="t-btn">{{ $post->exists ? 'Save changes' : 'Create post' }}</button>
         </div>
      </section>

      <section class="t-card">
         <div class="t-card__head"><h2>Cover image</h2></div>
         <div class="t-card__body">
            <template x-if="preview && !remove">
               <img :src="preview" alt="" class="t-image-preview">
            </template>
            <template x-if="!preview || remove">
               <div class="t-image-preview" style="display:grid;place-items:center;color:var(--t-soft)"><x-icon name="image" size="32" /></div>
            </template>
            <div class="t-field" style="margin-top:16px">
               <label for="f-image" class="t-label">Upload image</label>
               <input id="f-image" type="file" name="image" accept="image/jpeg,image/png,image/webp" class="t-input @error('image') is-invalid @enderror" style="padding:8px 10px"
                      @change="const f = $event.target.files[0]; if (f) { preview = URL.createObjectURL(f); remove = false }">
               @error('image')<span class="t-error">{{ $message }}</span>@else<span class="t-help">JPG, PNG or WebP up to 3 MB. Landscape (3:2) works best.</span>@enderror
            </div>
            <x-input name="image_alt" label="Alt text" :value="$post->image_alt" maxlength="200" placeholder="Describe the image for screen readers" />
            @if ($post->image)
               <div class="t-field">
                  <label class="t-check"><input type="checkbox" name="remove_image" value="1" x-model="remove"> Remove current image</label>
               </div>
            @endif
         </div>
      </section>
   </div>
</form>
@endsection
