<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Published blog posts, shaped like the website's `Post` type (frontend/app/data/blog.ts). */
class PostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(50, max(1, $request->integer('per_page', 12)));

        $posts = Post::published()
            ->when($request->string('category')->toString(), fn ($q, $c) => $q->where('category', $c))
            ->orderByDesc('published_at')->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'ok' => true,
            'data' => $posts->getCollection()->map(fn (Post $p) => $this->present($p, false)),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $post = Post::published()->where('slug', $slug)->first();

        if (! $post) {
            return response()->json(['ok' => false, 'error' => 'Post not found.'], 404);
        }

        return response()->json(['ok' => true, 'data' => $this->present($post, true)]);
    }

    public function categories(): JsonResponse
    {
        return response()->json(['ok' => true, 'data' => config('thor.blog_categories')]);
    }

    private function present(Post $post, bool $withBody): array
    {
        return [
            'slug' => $post->slug,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'category' => $post->category,
            'date' => $post->published_at?->toDateString(),
            // Website-relative paths (/images/...) stay relative so the site serves them itself.
            'image' => str_starts_with((string) $post->image, '/') ? $post->image : $post->imageUrl(),
            'alt' => $post->image_alt ?: $post->title,
            'author' => $post->author,
            'featured' => $post->is_featured,
            'read' => $post->readingTime(),
        ] + ($withBody ? ['body' => $post->body] : []);
    }
}
