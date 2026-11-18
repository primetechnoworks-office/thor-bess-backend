<?php

namespace Tests\Feature;

use App\Mail\LeadReceived;
use App\Models\Lead;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeadApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config(['thor.notify.default' => ['sales@example.com']]);
    }

    public function test_datasheet_request_is_saved_and_emailed(): void
    {
        Product::create(['slug' => 'thor-lfp-12v-100ah', 'name' => '"ThOR" LFP 12V 100Ah', 'series' => '12V', 'voltage' => 12, 'capacity_label' => '100 Ah',
            'variants' => [['label' => '100 Ah', 'capacity_ah' => 100, 'energy' => '1.4 kWh', 'price' => 19799]], 'cycles' => 4000, 'service_life' => 9,
            'guarantee' => 3, 'warranty' => 5, 'summary' => 'S', 'best_for' => [], 'images' => ['/images/x.webp']]);

        $this->postJson('/api/leads/datasheet', [
            'name' => 'Asha Rao', 'company' => 'Sunrise Solar', 'email' => 'asha@sunrise.in', 'phone' => '98765 43210',
            'product' => 'thor-lfp-12v-100ah', 'location' => 'Pune',
        ])->assertOk()->assertJson(['ok' => true]);

        $lead = Lead::sole();
        $this->assertSame('datasheet', $lead->type);
        $this->assertSame('"ThOR" LFP 12V 100Ah', $lead->subject);
        $this->assertSame('Pune', $lead->details['location']);
        Mail::assertSent(LeadReceived::class, fn ($mail) => $mail->hasTo('sales@example.com') && $mail->hasReplyTo('asha@sunrise.in'));
    }

    public function test_quote_request_filters_unknown_charging_sources(): void
    {
        $this->postJson('/api/leads/quote', [
            'segment' => 'medium', 'application' => 'Hospital / medical', 'sources' => ['Solar', 'Nuclear'],
            'name' => 'Dr Mehta', 'email' => 'mehta@hospital.org', 'phone' => '+91 99887 76655',
        ])->assertOk();

        $this->assertSame(['Solar'], Lead::sole()->details['sources']);
    }

    public function test_contact_enquiry_with_attachment(): void
    {
        Storage::fake('local');

        $this->post('/api/leads/contact', [
            'organization' => 'Acme Infra', 'name' => 'Ravi', 'email' => 'ravi@acme.in', 'phone' => '9876543210',
            'dial' => '+971', 'segment' => 'Industrial', 'message' => 'Need 500 kWh BESS',
            'attachment' => UploadedFile::fake()->create('site plan.pdf', 200, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertOk();

        $lead = Lead::sole();
        $this->assertSame('+971 9876543210', $lead->phone);
        $this->assertSame('Industrial', $lead->subject);
        Storage::disk('local')->assertExists($lead->attachment_path);
    }

    public function test_validation_errors_use_the_website_shape(): void
    {
        $this->postJson('/api/leads/datasheet', ['name' => 'X'])
            ->assertStatus(422)->assertExactJson(['ok' => false, 'error' => 'Please fill in all required fields.']);

        $this->postJson('/api/leads/datasheet', [
            'name' => 'X', 'company' => 'Y', 'email' => 'not-an-email', 'phone' => '9876543210', 'product' => 'thor-e-generator',
        ])->assertStatus(422)->assertJson(['error' => 'Please enter a valid email address.']);

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_honeypot_pretends_success_without_saving(): void
    {
        $this->postJson('/api/leads/quote', ['website' => 'http://spam.example'])->assertOk()->assertJson(['ok' => true]);
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_submissions_are_rate_limited(): void
    {
        foreach (range(1, 5) as $i) {
            $this->postJson('/api/leads/quote', ['website' => 'x'])->assertOk();
        }
        $this->postJson('/api/leads/quote', ['website' => 'x'])->assertStatus(429)->assertJson(['ok' => false]);
    }
}
