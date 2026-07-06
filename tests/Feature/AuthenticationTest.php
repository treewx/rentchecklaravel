<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_renders(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Landlord',
            'email' => 'landlord@example.com',
            'password' => 'secret-password-1',
            'password_confirmation' => 'secret-password-1',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'landlord@example.com']);
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_rejects_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_unverified_user_is_redirected_to_verification_notice(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect('/email/verify');
    }

    public function test_verified_user_can_reach_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_password_reset_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'new-secret-password',
                'password_confirmation' => 'new-secret-password',
            ]);

            $response->assertSessionHasNoErrors();

            $this->assertTrue(Hash::check('new-secret-password', $user->fresh()->password));

            return true;
        });
    }

    public function test_user_without_two_factor_is_redirected_to_setup_when_required(): void
    {
        config(['fortify.require_two_factor' => true]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect('/two-factor/setup');
    }

    public function test_two_factor_setup_page_is_reachable_without_two_factor(): void
    {
        config(['fortify.require_two_factor' => true]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/two-factor/setup')
            ->assertOk();
    }

    public function test_user_can_delete_account_with_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete('/settings/account', [
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_account_deletion_requires_correct_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/settings')
            ->delete('/settings/account', ['password' => 'wrong-password'])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
