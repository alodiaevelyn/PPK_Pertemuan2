<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Landing page mengembalikan respons sukses 200 OK.
     */
    public function test_landing_page_returns_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('JARA');
    }

    /**
     * Pengguna dapat mendaftar akun baru.
     */
    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('users', [
            'email' => 'budi@example.com',
            'name' => 'Budi Santoso',
        ]);
    }

    /**
     * Pengguna dapat login dan logout.
     */
    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => 'secret123',
        ]);

        $loginResponse = $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'secret123',
        ]);

        $loginResponse->assertRedirect('/lists');
        $this->assertAuthenticatedAs($user);

        $logoutResponse = $this->post('/logout');
        $logoutResponse->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * Login gagal jika kredensial salah.
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => 'secret123',
        ]);

        $response = $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Admin dapat mengakses halaman manajemen pengguna.
     */
    public function test_admin_can_view_users_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk();
        $response->assertSee('Manajemen Pengguna');
    }

    /**
     * Pengguna non-admin dilarang mengakses halaman admin (403).
     */
    public function test_non_admin_cannot_access_admin_users(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertForbidden();
    }
}
