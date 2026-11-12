<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SanctumAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_protected_endpoints(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
        $this->putJson('/api/me', [])->assertUnauthorized();
        $this->postJson('/api/logout')->assertUnauthorized();
    }

    public function test_login_uses_a_session_instead_of_returning_a_token(): void
    {
        $user = $this->createUser();

        $this->withHeader('Origin', 'http://localhost:3000')
            ->postJson('/api/login', [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonMissingPath('token');

        $this->assertAuthenticatedAs($user, 'web');
        $this->getJson('/api/me')->assertOk()->assertJsonPath('id', $user->id);
        $this->postJson('/api/logout')->assertOk();
        $this->assertGuest('web');
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $user = $this->createUser();

        $this->withHeader('Origin', 'http://localhost:3000')
            ->postJson('/api/login', [
                'email' => $user->email,
                'password' => 'incorrect-password',
            ])->assertUnauthorized();

        $this->assertGuest('web');
    }

    public function test_user_can_update_their_own_email_without_a_jwt_guard(): void
    {
        $user = $this->createUser();

        $this->actingAs($user, 'web')->putJson('/api/me', [
            'name' => 'Updated name',
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()->assertJsonPath('user.name', 'Updated name');
    }

    private function createUser(): User
    {
        $user = User::factory()->make();
        unset($user->email_verified_at, $user->remember_token);
        $user->save();

        return $user;
    }
}
