<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get(route('login'))->assertOk()->assertSee('Sign in to your account');
    }

    public function test_user_can_sign_in_and_out(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);

        $this->get(route('admin.dashboard'))->assertOk()->assertSee($user->name);

        $this->post(route('admin.logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_wrong_password_is_rejected_and_rate_limited(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $i) {
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'nope'])->assertSessionHasErrors('email');
        }
        // Even the correct password is refused while locked out.
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertStringStartsWith('Too many login attempts', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_sign_in(): void
    {
        $user = User::factory()->inactive()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_deactivated_user_is_signed_out_mid_session(): void
    {
        $user = User::factory()->inactive()->create();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_password_reset_flow(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $this->get(route('password.reset', $notification->token))->assertOk();

            $this->post(route('password.store'), [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'new-secret-123',
                'password_confirmation' => 'new-secret-123',
            ])->assertRedirect(route('login'));

            return Hash::check('new-secret-123', $user->fresh()->password);
        });
    }

    public function test_unknown_email_gets_the_same_reset_message(): void
    {
        $this->post(route('password.email'), ['email' => 'nobody@example.com'])->assertSessionHas('status');
    }

    public function test_registration_is_disabled_by_default(): void
    {
        $this->get(route('register'))->assertNotFound();
    }

    public function test_registration_creates_an_editor_when_enabled(): void
    {
        config(['thor.allow_registration' => true]);

        $this->post(route('register.store'), [
            'name' => 'New Editor', 'email' => 'editor@example.com',
            'password' => 'secret-pass-1', 'password_confirmation' => 'secret-pass-1', 'terms' => '1',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('users', ['email' => 'editor@example.com', 'role' => User::ROLE_EDITOR]);
    }

    public function test_profile_and_password_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('admin.profile.update'), ['name' => 'Renamed', 'email' => $user->email, 'phone' => '+91 98765 43210'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Renamed', $user->fresh()->name);

        $this->put(route('admin.profile.password'), ['current_password' => 'wrong', 'password' => 'brand-new-1', 'password_confirmation' => 'brand-new-1'])
            ->assertSessionHasErrorsIn('password', 'current_password');

        $this->put(route('admin.profile.password'), ['current_password' => 'password', 'password' => 'brand-new-1', 'password_confirmation' => 'brand-new-1'])
            ->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('brand-new-1', $user->fresh()->password));
    }

    public function test_api_token_login_me_and_logout(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'wrong'])->assertStatus(422)->assertJson(['ok' => false]);

        $token = $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()->assertJsonPath('user.email', $user->email)->json('token');

        $this->withToken($token)->getJson('/api/auth/me')->assertOk()->assertJsonPath('user.id', $user->id);
        $this->withToken($token)->postJson('/api/auth/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
