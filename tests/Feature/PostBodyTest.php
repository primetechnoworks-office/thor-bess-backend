<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Support\PostBody;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostBodyTest extends TestCase
{
    use RefreshDatabase;

    public function test_dangerous_markup_is_removed_and_editor_markup_kept(): void
    {
        $html = PostBody::normalize(
            '<p class="callout evil" style="color:red" onclick="x()">Tip<script>alert(1)</script></p>'
            .'<a href="javascript:alert(1)">bad</a><a href="https://example.com" target="_blank">good</a>'
            .'<iframe src="https://evil.test"></iframe><img src="x.png" onerror="alert(1)" alt="A">'
            .'<ul><li data-list-item-id="abc">One</li></ul><figure class="table"><table><tbody><tr><td colspan="2">x</td></tr></tbody></table></figure>'
        );

        $this->assertStringContainsString('<p class="callout">Tip</p>', $html);
        $this->assertStringContainsString('<a href="https://example.com" target="_blank" rel="noopener noreferrer">good</a>', $html);
        $this->assertStringContainsString('<li>One</li>', $html);
        $this->assertStringContainsString('<td colspan="2">x</td>', $html);
        foreach (['script', 'javascript:', 'onclick', 'onerror', 'style=', 'iframe', 'evil', 'data-list-item-id'] as $bad) {
            $this->assertStringNotContainsString($bad, $html);
        }
    }

    public function test_old_text_format_is_converted_to_html(): void
    {
        $html = PostBody::normalize("Intro with **bold** & more.\n\n## Why LFP\n### Detail\n- one\n- two\n> A quote\n!! A tip");

        $this->assertSame(
            "<p>Intro with <strong>bold</strong> &amp; more.</p>\n<h2>Why LFP</h2>\n<h3>Detail</h3>\n<ul><li>one</li><li>two</li></ul>\n"
            ."<blockquote><p>A quote</p></blockquote>\n<p class=\"callout\">A tip</p>",
            $html
        );
    }

    public function test_bodies_are_cleaned_whenever_a_post_is_saved(): void
    {
        $post = Post::create(['title' => 'T', 'slug' => 't', 'category' => 'Company News', 'excerpt' => 'E', 'body' => '<h2>Heading</h2><script>x</script>']);
        $this->assertSame('<h2>Heading</h2>', $post->fresh()->body);

        // In the old text format, markup is plain text, so it's escaped (shown as text, never run).
        $post->update(['body' => "## Heading\n<script>x</script>"]);
        $this->assertSame("<h2>Heading</h2>\n<p>&lt;script&gt;x&lt;/script&gt;</p>", $post->fresh()->body);
    }

    public function test_an_empty_editor_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.posts.store'), ['title' => 'T', 'category' => 'Company News', 'excerpt' => 'E', 'author' => 'A', 'status' => 'draft',
                'body' => '<p>&nbsp;</p><p><script>alert(1)</script></p>'])
            ->assertSessionHasErrors(['body' => 'Please write the article before saving.']);
    }

    public function test_editor_image_upload(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $url = $this->actingAs($user)
            ->postJson(route('admin.posts.images'), ['upload' => UploadedFile::fake()->image('photo.jpg', 800, 400)])
            ->assertOk()->json('url');
        Storage::disk('public')->assertExists(str_replace('/storage/', '', parse_url($url, PHP_URL_PATH)));

        $this->postJson(route('admin.posts.images'), ['upload' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')])
            ->assertStatus(422)->assertJsonStructure(['error' => ['message']]);
    }

    public function test_guests_cannot_upload_images(): void
    {
        $this->postJson(route('admin.posts.images'), ['upload' => UploadedFile::fake()->image('photo.jpg')])->assertUnauthorized();
    }
}
