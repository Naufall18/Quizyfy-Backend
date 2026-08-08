<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit test AuthController:
 * register, login, logout, user, updateProfile
 */
class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    // ─── Register ────────────────────────────────────────────────

    public function test_user_can_register_as_siswa(): void
    {
        $res = $this->postJson('/api/register', [
            'name'                  => 'Siswa Baru',
            'email'                 => 'siswa@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'user',
        ]);

        $res->assertStatus(201)
            ->assertJsonFragment(['success' => true])
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);

        $this->assertDatabaseHas('users', ['email' => 'siswa@example.com', 'role' => 'user']);
    }

    public function test_user_can_register_as_guru(): void
    {
        $res = $this->postJson('/api/register', [
            'name'                  => 'Guru Baru',
            'email'                 => 'guru@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'guru',
        ]);

        $res->assertStatus(201)
            ->assertJsonFragment(['role' => 'guru']);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'duplikat@example.com']);

        $res = $this->postJson('/api/register', [
            'name'                  => 'User Duplikat',
            'email'                 => 'duplikat@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'user',
        ]);

        $res->assertStatus(422);
    }

    public function test_register_fails_with_password_mismatch(): void
    {
        $res = $this->postJson('/api/register', [
            'name'                  => 'User',
            'email'                 => 'user@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'salah',
            'role'                  => 'user',
        ]);

        $res->assertStatus(422);
    }

    public function test_register_fails_with_missing_fields(): void
    {
        $res = $this->postJson('/api/register', []);
        $res->assertStatus(422);
    }

    // ─── Login ───────────────────────────────────────────────────

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email'    => 'user@test.com',
            'password' => bcrypt('secret123'),
            'role'     => 'user',
        ]);

        $res = $this->postJson('/api/login', [
            'email'    => 'user@test.com',
            'password' => 'secret123',
        ]);

        $res->assertStatus(200)
            ->assertJsonFragment(['success' => true])
            ->assertJsonStructure(['token', 'user' => ['id', 'email', 'role']]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email'    => 'user@test.com',
            'password' => bcrypt('benar'),
        ]);

        $res = $this->postJson('/api/login', [
            'email'    => 'user@test.com',
            'password' => 'salah',
        ]);

        $res->assertStatus(401)
            ->assertJsonFragment(['success' => false]);
    }

    public function test_login_fails_with_unknown_email(): void
    {
        $res = $this->postJson('/api/login', [
            'email'    => 'tidakada@example.com',
            'password' => 'apapun',
        ]);

        $res->assertStatus(401);
    }

    public function test_login_fails_with_missing_fields(): void
    {
        $res = $this->postJson('/api/login', []);
        $res->assertStatus(422);
    }

    // ─── Logout ──────────────────────────────────────────────────

    public function test_authenticated_user_can_logout(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $res = $this->withToken($token)->postJson('/api/logout');
        $res->assertStatus(200)
            ->assertJsonFragment(['success' => true]);
    }

    public function test_logout_requires_authentication(): void
    {
        $res = $this->postJson('/api/logout');
        $res->assertStatus(401);
    }

    // ─── User (GET /api/user) ─────────────────────────────────────

    public function test_authenticated_user_can_get_profile(): void
    {
        $user  = User::factory()->create(['name' => 'Coba User', 'role' => 'user']);
        $token = $user->createToken('test')->plainTextToken;

        $res = $this->withToken($token)->getJson('/api/user');
        $res->assertStatus(200)
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonPath('user.role', 'user');
    }

    public function test_unauthenticated_cannot_get_profile(): void
    {
        $res = $this->getJson('/api/user');
        $res->assertStatus(401);
    }

    // ─── Update Profile ──────────────────────────────────────────

    public function test_user_can_update_profile(): void
    {
        $user  = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('test')->plainTextToken;

        $res = $this->withToken($token)->putJson('/api/update-profile', [
            'name'  => 'Nama Baru',
            'email' => $user->email,
        ]);

        $res->assertStatus(200);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Nama Baru']);
    }

    public function test_update_profile_requires_authentication(): void
    {
        $res = $this->putJson('/api/update-profile', ['name' => 'Test']);
        $res->assertStatus(401);
    }
}
