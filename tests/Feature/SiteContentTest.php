<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use Database\Seeders\SiteContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteContentTest extends TestCase
{
    use RefreshDatabase;

    private const PRODUCT = [
        'name' => '"ThOR" LFP 12V 200Ah', 'series' => '12V', 'capacity_label' => '200 Ah', 'summary' => 'Big battery.',
        'best_for' => "Homes\n\n Offices ", 'cycles' => 4000, 'service_life' => 9, 'guarantee' => 3, 'warranty' => 5, 'sort_order' => 5, 'is_active' => '1',
        'variants' => [['label' => '200 Ah', 'capacity_ah' => '200', 'energy' => '2.4 kWh', 'price' => '29999'], ['label' => '150 Ah', 'capacity_ah' => '150', 'energy' => '1.8 kWh', 'price' => '24999']],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(SiteContentSeeder::class);
    }

    public function test_seeder_imports_the_website_content(): void
    {
        $this->assertSame(10, Product::count());
        $this->assertSame(3, Testimonial::count());
        $this->assertSame(10, TeamMember::count());
        $this->assertSame('+91 83497 74339', Setting::get(Setting::COMPANY)['phone']);
    }

    public function test_admin_pages_render(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        foreach ([
            route('admin.products.index'), route('admin.products.create'), route('admin.products.edit', Product::first()),
            route('admin.testimonials.index'), route('admin.testimonials.create'), route('admin.testimonials.edit', Testimonial::first()),
            route('admin.team.index'), route('admin.team.create'), route('admin.team.edit', TeamMember::first()),
            route('admin.settings.edit'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_product_create_update_and_api(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('admin.products.store'), self::PRODUCT + ['new_images' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')]])
            ->assertSessionHasNoErrors();
        $product = Product::where('slug', 'thor-lfp-12v-200ah')->firstOrFail();
        $this->assertSame(['Homes', 'Offices'], $product->best_for);
        $this->assertSame(24999, $product->fromPrice());
        $this->assertSame(12, $product->voltage);
        $this->assertCount(2, $product->images);

        // Reorder (second photo first), drop the first, rename: slug stays, removed upload is deleted.
        [$first, $second] = $product->images;
        $this->put(route('admin.products.update', $product), ['name' => 'Renamed', 'images' => [$second]] + self::PRODUCT)
            ->assertSessionHasNoErrors();
        $product->refresh();
        $this->assertSame('thor-lfp-12v-200ah', $product->slug);
        $this->assertSame([$second], $product->images);
        Storage::disk('public')->assertMissing($first);

        $api = $this->getJson('/api/products/thor-lfp-12v-200ah')->assertOk()->json('data');
        $this->assertSame('200 Ah', $api['capacityLabel']);
        $this->assertSame(29999, $api['variants'][0]['price']);
        $this->assertStringStartsWith('http', $api['images'][0]);
        $this->getJson('/api/products/datasheet-options')->assertJsonFragment(['value' => 'thor-lfp-12v-200ah'])->assertJsonFragment(['value' => 'thor-e-generator']);
    }

    public function test_products_need_a_photo_and_only_their_own_images(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('admin.products.store'), self::PRODUCT)->assertSessionHasErrors('new_images');
        $this->post(route('admin.products.store'), self::PRODUCT + ['images' => ['/images/other.webp']])->assertSessionHasErrors('images.0');
        $this->post(route('admin.products.store'), ['variants' => []] + self::PRODUCT)->assertSessionHasErrors('variants');
    }

    public function test_hidden_products_leave_the_website_and_datasheet_form(): void
    {
        Product::where('slug', 'thor-lfp-12v-100ah')->update(['is_active' => false]);

        $this->getJson('/api/products/thor-lfp-12v-100ah')->assertNotFound();
        $this->assertNotContains('thor-lfp-12v-100ah', array_column($this->getJson('/api/products')->json('data'), 'slug'));
        $this->postJson('/api/leads/datasheet', ['name' => 'A', 'company' => 'B', 'email' => 'a@b.co', 'phone' => '9876543210', 'product' => 'thor-lfp-12v-100ah'])
            ->assertStatus(422)->assertJson(['error' => 'Please select a product.']);
    }

    public function test_testimonials_and_team_with_photos(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('admin.testimonials.store'), ['quote' => 'Great.', 'name' => 'Asha', 'role' => 'CEO', 'sort_order' => 1, 'is_active' => '1',
            'photo' => UploadedFile::fake()->image('a.jpg')])->assertSessionHasNoErrors();
        $this->assertSame('Asha', $this->getJson('/api/testimonials')->json('data.0.name'));

        $this->post(route('admin.team.store'), ['name' => 'Ravi', 'role' => 'CTO', 'linkedin' => 'https://evil.example.com', 'sort_order' => 1])
            ->assertSessionHasErrors('linkedin');
        $this->post(route('admin.team.store'), ['name' => 'Ravi', 'role' => 'CTO', 'linkedin' => 'https://www.linkedin.com/in/ravi', 'sort_order' => 1, 'is_active' => '0'])
            ->assertSessionHasNoErrors();
        $this->assertNotContains('Ravi', array_column($this->getJson('/api/team')->json('data'), 'name'));

        $member = TeamMember::where('name', 'Ravi')->first();
        $this->put(route('admin.team.update', $member), ['name' => 'Ravi', 'role' => 'CTO', 'sort_order' => 1, 'is_active' => '1', 'photo' => UploadedFile::fake()->image('r.jpg')]);
        $this->assertContains('Ravi', array_column($this->getJson('/api/team')->json('data'), 'name'));
        Storage::disk('public')->assertExists($member->fresh()->image);
    }

    public function test_company_settings_admin_only_and_saved(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.settings.edit'))->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())->put(route('admin.settings.update'), [
            'phone' => '+91 90000 00000', 'email' => 'hello@thor.test', 'sales_email' => 'sales@thor.test', 'address' => "Line 1\nLine 2",
            'offices' => [['label' => 'HQ', 'lines' => "A\nB", 'map' => 'https://evil.test/frame', 'directions' => '']],
        ])->assertSessionHasErrors('offices.0.map');

        $this->put(route('admin.settings.update'), [
            'phone' => '+91 90000 00000', 'email' => 'hello@thor.test', 'sales_email' => 'sales@thor.test', 'address' => "Line 1\nLine 2",
            'offices' => [['label' => 'HQ', 'lines' => "A\n\nB", 'map' => 'https://www.google.com/maps?q=x&output=embed', 'directions' => '']],
            'socials' => ['facebook' => 'https://facebook.com/thor', 'myspace' => 'https://myspace.com/x'],
        ])->assertSessionHasNoErrors();

        $s = $this->getJson('/api/settings')->json('data');
        $this->assertSame('+91 90000 00000', $s['phone']);
        $this->assertSame(['Line 1', 'Line 2'], $s['address']);
        $this->assertSame(['A', 'B'], $s['offices'][0]['lines']);
        $this->assertSame(['facebook' => 'https://facebook.com/thor'], $s['socials']);
    }
}
