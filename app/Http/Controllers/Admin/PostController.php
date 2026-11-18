<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Support\PostBody;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

        $post = $this->persist($data);

        return redirect()->route('admin.posts.edit', $post)->with('success', 'Post created.');
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.form', ['post' => $post]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $this->persist($this->validated($request, $post), $post);

        return back()->with('success', 'Post saved.');
    }

    /**
     * Image upload for the article editor (CKEditor's SimpleUploadAdapter).
     * Responds with { url } on success or { error: { message } } as the editor expects.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'upload' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:3072'],
        ], ['upload.max' => 'Images must be 3 MB or smaller.', 'upload.mimes' => 'Please upload a JPG, PNG, WebP or GIF image.']);

        if ($validator->fails()) {
            return response()->json(['error' => ['message' => $validator->errors()->first()]], 422);
        }

        $path = $request->file('upload')->store('posts/content', 'public');

        return response()->json(['url' => Storage::disk('public')->url($path)]);
    }

    /**
     * Creates or updates a post. uniqueSlug() already picks a free slug (title, title-2, title-3, …),
     * but if two posts with the same title are saved at the same moment both can pick the same one;
     * the database's unique index rejects the second, so it takes the next free slug and tries again.
     */
    private function persist(array $data, ?Post $post = null): Post
    {
        for ($attempt = 1; ; $attempt++) {
            try {
                return $post ? tap($post)->update($data) : Post::create($data);
            } catch (UniqueConstraintViolationException $e) {
                if ($attempt >= 5 || ! isset($data['slug'])) {
                    throw $e;
                }
                $data['slug'] = Post::uniqueSlug($data['title'], $post?->id);
            }
        }
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
            'title' => ['required', 'string', 'max:120'],
            'category' => ['required', Rule::in(config('thor.blog_categories'))],
            'excerpt' => ['required', 'string', 'max:250'],
            'body' => ['required', 'string'],
            'author' => ['required', 'string', 'max:80'],
            'status' => ['required', Rule::in([Post::STATUS_DRAFT, Post::STATUS_PUBLISHED])],
            'published_at' => ['nullable', 'date', Rule::requiredIf($request->input('status') === Post::STATUS_PUBLISHED)],
            'is_featured' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'image_alt' => ['nullable', 'string', 'max:200'],
            'remove_image' => ['nullable', 'boolean'],
        ], ['published_at.required' => 'Choose a publish date to publish this post.']);

        // An editor that only contains empty paragraphs or stripped markup counts as empty.
        $visible = html_entity_decode(strip_tags(PostBody::normalize($data['body']), '<img>'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (preg_replace('/[\s\x{00A0}]+/u', '', $visible) === '') {
            throw ValidationException::withMessages(['body' => 'Please write the article before saving.']);
        }

        // The web address comes from the title. A post that's already live keeps its address
        // so links shared on social media, emails and search results don't break.
        if (! $post?->isLive()) {
            $data['slug'] = Post::uniqueSlug($data['title'], $post?->id);
        }
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
