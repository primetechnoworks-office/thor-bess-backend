<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\Milestone;
use App\Models\Partner;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\SiteContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteContentPhase2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(SiteContentSeeder::class);
    }

    public function test_seeder_imports_phase2_content(): void
    {
        $this->assertSame([4, 6, 4], [Milestone::count(), Faq::count(), Partner::count()]);
        $this->assertCount(4, Setting::get(Setting::INCUBATORS));
        $this->assertSame('DIPP208347', Setting::get(Setting::CREDENTIALS)['dipp']);
        $this->assertSame('RJ4JdZIgC6U', Setting::get(Setting::HERO)['youtube_id']);
        $this->assertCount(6, Setting::get(Setting::BANNERS));
    }

    public function test_admin_pages_render_and_credentials_are_admin_only(): void
    {
        $this->actingAs(User::factory()->create());
        foreach ([
            route('admin.milestones.index'), route('admin.milestones.create'), route('admin.milestones.edit', Milestone::first()),
            route('admin.faqs.index'), route('admin.faqs.create'), route('admin.faqs.edit', Faq::first()),
            route('admin.partners.index'), route('admin.partners.create'), route('admin.partners.edit', Partner::first()),
            route('admin.media.edit'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get(route('admin.credentials.edit'))->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.credentials.edit'))->assertOk();
    }

    public function test_milestone_and_faq_crud_feed_the_api(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('admin.milestones.store'), ['tag' => 'Telecom', 'title' => 'Tower backup', 'copy' => 'Copy', 'sort_order' => 1, 'is_active' => '1',
            'photo' => UploadedFile::fake()->image('m.jpg')])->assertSessionHasNoErrors();
        $first = $this->getJson('/api/milestones')->json('data.0');
        $this->assertSame('Tower backup', $first['title']);
        $this->assertStringStartsWith('http', $first['image']);

        $this->post(route('admin.faqs.store'), ['page' => 'nowhere', 'question' => 'Q', 'answer' => 'A', 'sort_order' => 1])->assertSessionHasErrors('page');
        $this->post(route('admin.faqs.store'), ['page' => 'e-generator', 'question' => 'Is it loud?', 'answer' => 'No.', 'sort_order' => 1, 'is_active' => '1'])
            ->assertSessionHasNoErrors();
        $this->assertSame(['q' => 'Is it loud?', 'a' => 'No.'], array_intersect_key($this->getJson('/api/faqs?page=e-generator')->json('data.0'), ['q' => 1, 'a' => 1]));

        Faq::where('question', 'Is it loud?')->update(['is_active' => false]);
        $this->assertNotContains('Is it loud?', array_column($this->getJson('/api/faqs?page=e-generator')->json('data'), 'q'));
    }

    public function test_partner_logo_rules_and_incubators(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('admin.partners.store'), ['name' => 'No logo', 'sort_order' => 1])->assertSessionHasErrors('photo');
        $svg = UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
        $this->post(route('admin.partners.store'), ['name' => 'SVG', 'sort_order' => 1, 'photo' => $svg])->assertSessionHasErrors('photo');
        $this->post(route('admin.partners.store'), ['name' => 'IIT', 'url' => 'https://iit.example', 'sort_order' => 1, 'is_active' => '1',
            'photo' => UploadedFile::fake()->image('logo.png', 400, 120)])->assertSessionHasNoErrors();

        $this->put(route('admin.partners.incubators'), ['incubators' => "One\n\n Two "])->assertSessionHasNoErrors();

        $api = $this->getJson('/api/partners')->json('data');
        $this->assertSame('IIT', $api['logos'][0]['alt']);
        $this->assertSame(['One', 'Two'], $api['incubators']);
    }

    public function test_credentials_update_and_certificate_upload(): void
    {
        $this->actingAs(User::factory()->admin()->create())->put(route('admin.credentials.update'), [
            'dipp' => 'DIPP1', 'patent' => 'P-42', 'awards' => "Award A\nAward B",
            'certificate_file' => UploadedFile::fake()->image('cert.jpg', 1600, 1131),
        ])->assertSessionHasNoErrors();

        $c = $this->getJson('/api/credentials')->json('data');
        $this->assertSame(['DIPP1', 'P-42', ['Award A', 'Award B']], [$c['dipp'], $c['patent'], $c['awards']]);
        $this->assertSame($c['certificate'], $c['certificate_hd']);
        $this->assertStringContainsString('/storage/credentials/', $c['certificate']);
    }

    public function test_hero_and_banners(): void
    {
        $this->actingAs(User::factory()->create());

        $this->put(route('admin.media.update'), ['youtube' => 'https://vimeo.com/1'])->assertSessionHasErrors('youtube');
        $this->put(route('admin.media.update'), ['youtube' => 'https://youtu.be/abcDEF12345?t=4',
            'banner_files' => ['contact' => UploadedFile::fake()->image('b.jpg', 1920, 600), 'not-a-page' => UploadedFile::fake()->image('x.jpg')]])
            ->assertSessionHasNoErrors();

        $media = $this->getJson('/api/media')->json('data');
        $this->assertSame('abcDEF12345', $media['hero']['youtubeId']);
        $this->assertStringContainsString('/storage/banners/', $media['banners']['contact']);
        $this->assertArrayNotHasKey('not-a-page', $media['banners']);
        $this->assertSame('/images/sections/solar-field-installation-banner.webp', $media['banners']['products']);

        $this->put(route('admin.media.update'), ['youtube' => '', 'video_file' => UploadedFile::fake()->create('film.mp4', 900, 'video/mp4')]);
        $this->assertStringContainsString('/storage/hero/', $this->getJson('/api/media')->json('data.hero.file'));
    }
}
