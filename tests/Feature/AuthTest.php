<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_protected_routes(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/products')->assertRedirect('/login');
        $this->get('/transactions')->assertRedirect('/login');
        $this->get('/reports')->assertRedirect('/login');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::query()->create([
            'name' => 'Fahd Administrator',
            'email' => 'admin@fahdims.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@fahdims.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::query()->create([
            'name' => 'Fahd Administrator',
            'email' => 'admin@fahdims.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'admin@fahdims.test',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login_or_access_dashboard(): void
    {
        $inactiveUser = User::query()->create([
            'name' => 'Nonaktif Staff',
            'email' => 'inactive@fahdims.test',
            'password' => 'password',
            'role' => 'staff',
            'is_active' => false,
        ]);

        $loginResponse = $this->from('/login')->post('/login', [
            'email' => 'inactive@fahdims.test',
            'password' => 'password',
        ]);

        $loginResponse->assertRedirect('/login');
        $loginResponse->assertSessionHasErrors('email');
        $this->assertGuest();

        $dashboardResponse = $this->actingAs($inactiveUser)->get('/dashboard');
        $dashboardResponse->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_logout_destroys_authenticated_session(): void
    {
        $user = User::query()->create([
            'name' => 'Staff Gudang',
            'email' => 'staff@fahdims.test',
            'password' => 'password',
            'role' => 'staff',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}

