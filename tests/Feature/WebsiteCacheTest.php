<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebsiteCacheTest extends TestCase
{
    use RefreshDatabase;

    private function makePost(): Post
    {
        return Post::create(['title' => 'Hello', 'slug' => 'hello', 'category' => 'Company News', 'excerpt' => 'Hi', 'body' => 'Body', 'status' => 'published', 'published_at' => now()]);
    }

    public function test_saving_and_deleting_a_post_refreshes_the_website(): void
    {
        config(['thor.revalidate_secret' => 'shh', 'thor.frontend_url' => 'http://site.test']);
        Http::fake(['site.test/*' => Http::response(['ok' => true])]);

        $post = $this->makePost();
        $post->delete();

        Http::assertSentCount(2);
        Http::assertSent(fn (Request $r) => $r->url() === 'http://site.test/api/revalidate'
            && $r->hasHeader('Authorization', 'Bearer shh') && $r['tag'] === 'posts');
    }

    public function test_no_secret_means_no_request(): void
    {
        config(['thor.revalidate_secret' => null]);
        Http::fake();

        $this->makePost();

        Http::assertNothingSent();
    }

    public function test_website_errors_never_break_saving(): void
    {
        config(['thor.revalidate_secret' => 'shh']);
        Http::fake(['*' => Http::response('down', 500)]);

        $this->assertTrue($this->makePost()->exists);
    }
}
