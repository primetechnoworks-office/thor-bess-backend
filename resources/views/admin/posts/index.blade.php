@extends('layouts.app')
@section('title', 'Blog posts')
@section('content')
<x-page-head title="Blog posts" kicker="Blog & News" description="Articles shown on the website's Blog page.">
   <a href="{{ route('admin.posts.create') }}" class="t-btn"><x-icon name="plus" /> New post</a>
</x-page-head>

<section class="t-card">
   <div class="t-card__head">
      <form method="GET" class="t-filters" style="width:100%">
         <div class="t-input-wrap">
            <x-icon name="search" />
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search titles…" class="t-input t-input--icon">
         </div>
         <select name="category" class="t-select" onchange="this.form.submit()" aria-label="Category">
            <option value="">All categories</option>
            @foreach (config('thor.blog_categories') as $category)
               <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>
            @endforeach
         </select>
         <select name="status" class="t-select" onchange="this.form.submit()" aria-label="Status">
            <option value="">Any status</option>
            <option value="published" @selected(($filters['status'] ?? '') === 'published')>Published</option>
            <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Draft</option>
         </select>
         <button type="submit" class="t-btn t-btn--navy">Filter</button>
         @if (array_filter($filters))
            <a href="{{ route('admin.posts.index') }}" class="t-btn t-btn--ghost">Clear</a>
         @endif
      </form>
   </div>

   @if ($posts->isEmpty())
      <div class="t-empty">
         <span class="t-empty__icon"><x-icon name="file-text" /></span>
         <h3>No posts found</h3>
         <p>Write your first article — it will appear on the website once published.</p>
      </div>
   @else
      <div class="t-table-wrap">
         <table class="t-table">
            <thead><tr><th>Post</th><th>Category</th><th>Status</th><th>Publish date</th><th class="t-right">Actions</th></tr></thead>
            <tbody>
               @foreach ($posts as $post)
                  <tr>
                     <td>
                        <div class="t-flex">
                           @if ($post->imageUrl())
                              <img src="{{ $post->imageUrl() }}" alt="" class="t-thumb" loading="lazy" onerror="this.style.visibility='hidden'">
                           @else
                              <span class="t-thumb" style="display:grid;place-items:center"><x-icon name="image" size="18" /></span>
                           @endif
                           <span style="min-width:0">
                              <a href="{{ route('admin.posts.edit', $post) }}" class="t-row-link">{{ Str::limit($post->title, 80) }}</a>
                              <span class="t-sub">/blog/{{ $post->slug }} @if ($post->is_featured)· <span style="color:var(--t-amber)">★ Featured</span>@endif</span>
                           </span>
                        </div>
                     </td>
                     <td class="t-nowrap">{{ $post->category }}</td>
                     <td>
                        @if ($post->isLive())
                           <span class="t-badge t-badge--success">Live</span>
                        @elseif ($post->status === 'published')
                           <span class="t-badge t-badge--info">Scheduled</span>
                        @else
                           <span class="t-badge t-badge--neutral">Draft</span>
                        @endif
                     </td>
                     <td class="t-nowrap">{{ $post->published_at?->format('d M Y') ?? '—' }}</td>
                     <td class="t-right t-nowrap" x-data>
                        @if ($post->isLive())
                           <a href="{{ config('thor.frontend_url') }}/blog/{{ $post->slug }}" target="_blank" rel="noopener" class="t-icon-action" title="View on website"><x-icon name="external" /></a>
                        @endif
                        <a href="{{ route('admin.posts.edit', $post) }}" class="t-icon-action" title="Edit"><x-icon name="pencil" /></a>
                        <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" style="display:inline" @submit="if (!confirm('Delete “{{ addslashes($post->title) }}”? This cannot be undone.')) $event.preventDefault()">
                           @csrf @method('DELETE')
                           <button type="submit" class="t-icon-action t-icon-action--danger" title="Delete"><x-icon name="trash" /></button>
                        </form>
                     </td>
                  </tr>
               @endforeach
            </tbody>
         </table>
      </div>
      @if ($posts->hasPages())
         <div class="t-card__foot">{{ $posts->links() }}</div>
      @endif
   @endif
</section>
@endsection
