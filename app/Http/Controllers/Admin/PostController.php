<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in([Post::STATUS_DRAFT, Post::STATUS_PUBLISHED])],
            'category' => ['nullable', 'string', 'max:60'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $posts = Post::query()
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['category'] ?? null, fn ($q, $c) => $q->where('category', $c))
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where('title', 'like', "%{$term}%"))
            ->orderByDesc('published_at')->orderByDesc('id')
            ->paginate(12)->withQueryString();

        return view('admin.posts.index', ['posts' => $posts, 'filters' => $filters]);
    }

    public function create(): View
    {
        return view('admin.posts.form', ['post' => new Post([
            'status' => Post::STATUS_DRAFT,
            'author' => '"ThOR" BESS Team',
            'published_at' => now(),
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;

        $post = Post::create($data);

        return redirect()->route('admin.posts.edit', $post)->with('success', 'Post created.');
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.form', ['post' => $post]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $post->update($this->validated($request, $post));

        return back()->with('success', 'Post saved.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        if ($post->isUploadedImage()) {
            Storage::disk('public')->delete($post->image);
        }
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Post deleted.');
    }

    private function validated(Request $request, ?Post $post = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200', 'alpha_dash', Rule::unique('posts', 'slug')->ignore($post?->id)],
            'category' => ['required', Rule::in(config('thor.blog_categories'))],
            'excerpt' => ['required', 'string', 'max:400'],
            'body' => ['required', 'string'],
            'author' => ['required', 'string', 'max:80'],
            'status' => ['required', Rule::in([Post::STATUS_DRAFT, Post::STATUS_PUBLISHED])],
            'published_at' => ['nullable', 'date', Rule::requiredIf($request->input('status') === Post::STATUS_PUBLISHED)],
            'is_featured' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'image_alt' => ['nullable', 'string', 'max:200'],
            'remove_image' => ['nullable', 'boolean'],
        ], ['published_at.required' => 'Choose a publish date to publish this post.']);

        $data['slug'] = Post::uniqueSlug(($data['slug'] ?? null) ?: $data['title'], $post?->id);
        $data['is_featured'] = $request->boolean('is_featured');

        unset($data['image'], $data['remove_image']);
        if ($request->hasFile('image') || $request->boolean('remove_image')) {
            if ($post?->isUploadedImage()) {
                Storage::disk('public')->delete($post->image);
            }
            $data['image'] = $request->hasFile('image') ? $request->file('image')->store('posts', 'public') : null;
        }

        // Only one featured article at a time, like the website's hero card.
        if ($data['is_featured']) {
            Post::where('is_featured', true)->when($post, fn ($q) => $q->whereKeyNot($post->id))->update(['is_featured' => false]);
        }

        return $data;
    }
}
