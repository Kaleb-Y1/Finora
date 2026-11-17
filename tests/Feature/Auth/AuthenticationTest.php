<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Finora');
        $response->assertSee('Masuk ke Akun Anda');
    }

    public function test_users_can_authenticate_using_username(): void
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => Hash::make('password123'),
            'role' => 'umum',
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'login' => 'testuser',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_users_can_authenticate_using_email(): void
    {
        $user = User::factory()->create([
            'email' => 'testuser@finora.local',
            'username' => 'testuser2',
            'password' => Hash::make('password123'),
            'role' => 'umum',
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'login' => 'testuser@finora.local',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'username' => 'wrongpassuser',
            'password' => Hash::make('correctpassword'),
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'login' => 'wrongpassuser',
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }

    public function test_inactive_users_are_rejected(): void
    {
        $user = User::factory()->create([
            'username' => 'inactiveuser',
            'password' => Hash::make('password123'),
            'status' => 'inactive',
        ]);

        $response = $this->post('/login', [
            'login' => 'inactiveuser',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }

    public function test_orang_tua_is_redirected_to_senior_dashboard(): void
    {
        $seniorUser = User::factory()->create([
            'username' => 'senioruser',
            'password' => Hash::make('password123'),
            'role' => 'orang_tua',
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'login' => 'senioruser',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard/senior');
    }

    public function test_user_can_logout_and_session_is_invalidated(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $umumUser = User::factory()->create([
            'role' => 'umum',
            'status' => 'active',
        ]);

        $response = $this->actingAs($umumUser)->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_routes(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($adminUser)->get('/admin/users');

        $response->assertStatus(200);
    }
}
