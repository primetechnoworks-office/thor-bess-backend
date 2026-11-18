<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostSlugTest extends TestCase
{
    use RefreshDatabase;

    private const FIELDS = ['category' => 'Company News', 'excerpt' => 'Short', 'body' => 'Body', 'author' => 'Team', 'status' => 'draft'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    private function create(string $title, array $extra = []): Post
    {
        $this->post(route('admin.posts.store'), self::FIELDS + $extra + ['title' => $title])->assertSessionHasNoErrors();

        return Post::latest('id')->first();
    }

    public function test_same_titles_get_numbered_slugs(): void
    {
        $slugs = collect(range(1, 4))->map(fn () => $this->create('Why LFP Batteries Last Longer')->slug);

        $this->assertSame(['why-lfp-batteries-last-longer', 'why-lfp-batteries-last-longer-2', 'why-lfp-batteries-last-longer-3', 'why-lfp-batteries-last-longer-4'], $slugs->all());
    }

    public function test_titles_that_only_differ_in_punctuation_or_case_are_still_unique(): void
    {
        $a = $this->create('Solar + Storage: A Guide');
        $b = $this->create('solar storage — a guide!');

        $this->assertSame('solar-storage-a-guide', $a->slug);
        $this->assertSame('solar-storage-a-guide-2', $b->slug);
    }

    public function test_reusing_a_live_posts_title_does_not_take_its_address(): void
    {
        $live = $this->create('Company News 2026', ['status' => 'published', 'published_at' => now()->toDateString()]);
        $copy = $this->create('Company News 2026');

        $this->assertSame('company-news-2026', $live->fresh()->slug);
        $this->assertSame('company-news-2026-2', $copy->slug);
    }

    public function test_renaming_a_draft_to_another_posts_title_gets_a_unique_slug(): void
    {
        $this->create('Battery Basics');
        $draft = $this->create('Something Else');

        $this->put(route('admin.posts.update', $draft), self::FIELDS + ['title' => 'Battery Basics']);
        $this->assertSame('battery-basics-2', $draft->fresh()->slug);

        // Saving again with the same title keeps its own slug instead of becoming -3.
        $this->put(route('admin.posts.update', $draft), self::FIELDS + ['title' => 'Battery Basics']);
        $this->assertSame('battery-basics-2', $draft->fresh()->slug);
    }

    public function test_a_simultaneous_save_with_the_same_title_retries_with_the_next_slug(): void
    {
        // Another admin's post with the same slug lands between our check and our insert.
        $raced = false;
        Post::creating(function (Post $post) use (&$raced) {
            if (! $raced) {
                $raced = true;
                DB::table('posts')->insert(['title' => 'Grid Storage', 'slug' => $post->slug, 'category' => 'Company News',
                    'excerpt' => 'x', 'body' => 'x', 'author' => 'x', 'status' => 'draft', 'created_at' => now(), 'updated_at' => now()]);
            }
        });

        $post = $this->create('Grid Storage');

        $this->assertSame('grid-storage-2', $post->slug);
        $this->assertSame(1, Post::where('slug', 'grid-storage')->count());
    }
}
