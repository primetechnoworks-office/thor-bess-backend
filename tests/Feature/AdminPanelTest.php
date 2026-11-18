<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function lead(array $attrs = []): Lead
    {
        return Lead::create($attrs + [
            'type' => 'contact', 'status' => 'new', 'name' => 'Ravi', 'company' => 'Acme',
            'email' => 'ravi@acme.in', 'phone' => '+91 9876543210', 'subject' => 'Industrial',
        ]);
    }

    public function test_every_admin_page_renders(): void
    {
        $admin = User::factory()->admin()->create();
        $lead = $this->lead();
        $post = Post::create(['title' => 'Hello', 'slug' => 'hello', 'category' => 'Company News', 'excerpt' => 'Hi', 'body' => 'Body', 'status' => 'published', 'published_at' => now()]);

        $this->actingAs($admin);
        foreach ([
            route('admin.dashboard'), route('admin.leads.index'), route('admin.leads.index', ['type' => 'contact', 'status' => 'new', 'q' => 'Ravi']),
            route('admin.leads.show', $lead), route('admin.posts.index'), route('admin.posts.create'), route('admin.posts.edit', $post),
            route('admin.users.index'), route('admin.users.create'), route('admin.users.edit', $admin), route('admin.profile.edit'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get(route('admin.leads.export'))->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_editors_cannot_manage_users(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_lead_status_update_sets_contacted_at(): void
    {
        $lead = $this->lead();

        $this->actingAs(User::factory()->create())
            ->put(route('admin.leads.update', $lead), ['status' => 'contacted', 'notes' => 'Called'])
            ->assertSessionHasNoErrors();

        $this->assertNotNull($lead->fresh()->contacted_at);
        $this->assertSame('Called', $lead->fresh()->notes);
    }

    public function test_post_crud_and_public_api(): void
    {
        $this->actingAs(User::factory()->create())->post(route('admin.posts.store'), [
            'title' => 'Why LFP?', 'category' => 'LFP Technology', 'excerpt' => 'Short', 'body' => "## Intro\nText",
            'author' => 'Team', 'status' => 'published', 'published_at' => now()->toDateString(), 'is_featured' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('posts', ['slug' => 'why-lfp', 'is_featured' => true]);

        $this->getJson('/api/posts')->assertOk()->assertJsonPath('data.0.slug', 'why-lfp');
        $this->getJson('/api/posts/why-lfp')->assertOk()->assertJsonPath('data.body', "<h2>Intro</h2>\n<p>Text</p>");

        Post::where('slug', 'why-lfp')->update(['status' => 'draft']);
        $this->getJson('/api/posts/why-lfp')->assertNotFound();
    }

    public function test_slug_is_generated_from_the_title_on_save(): void
    {
        $this->actingAs(User::factory()->create());
        $fields = ['category' => 'Company News', 'excerpt' => 'Short', 'body' => 'Body', 'author' => 'Team'];

        // New post: slug from the title, unique, and a submitted slug is ignored.
        $this->post(route('admin.posts.store'), $fields + ['title' => '"ThOR" Container: 2 MWh @ Solar Parks', 'status' => 'draft', 'slug' => 'hacked']);
        $this->post(route('admin.posts.store'), $fields + ['title' => '"ThOR" Container: 2 MWh @ Solar Parks', 'status' => 'draft']);
        $this->assertSame(['thor-container-2-mwh-at-solar-parks', 'thor-container-2-mwh-at-solar-parks-2'], Post::orderBy('id')->pluck('slug')->all());

        // Draft: renaming updates the slug.
        $draft = Post::orderBy('id')->first();
        $this->put(route('admin.posts.update', $draft), $fields + ['title' => 'Renamed Draft', 'status' => 'draft']);
        $this->assertSame('renamed-draft', $draft->fresh()->slug);

        // Live post: renaming keeps the published address.
        $this->put(route('admin.posts.update', $draft), $fields + ['title' => 'Renamed Draft', 'status' => 'published', 'published_at' => now()->toDateString()]);
        $this->put(route('admin.posts.update', $draft), $fields + ['title' => 'Better Title', 'status' => 'published', 'published_at' => now()->toDateString()]);
        $this->assertSame('renamed-draft', $draft->fresh()->slug);
        $this->assertSame('Better Title', $draft->fresh()->title);
    }

    public function test_admin_cannot_delete_or_demote_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertSessionHas('error');
        $this->put(route('admin.users.update', $admin), ['name' => 'Me', 'email' => $admin->email, 'role' => 'editor', 'is_active' => '0']);

        $this->assertTrue($admin->fresh()->isAdmin());
        $this->assertTrue($admin->fresh()->is_active);
    }
}
