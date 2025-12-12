<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    /** @test */
    public function login_success_and_receive_token()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        
        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in'
        ]);
    }

    /** @test */
    public function login_fails_with_invalid_password()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'WRONGPASSWORD',
        ]);

        $response->assertStatus(401);

        $response->assertJson([
            'error' => 'Unauthorized'
        ]);

        $response->assertJsonMissing(['access_token']);
    }

    /** @test */
    public function access_protected_route_success()
    {
        $user = User::factory()->create();

        $token = auth()->login($user);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->getJson('/api/auth/user-profile');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id', 'name', 'email'
        ]);
    }

    /** @test */
    public function access_protected_route_fails_without_token()
    {
        $response = $this->getJson('/api/auth/user-profile');

        $response->assertStatus(401);

        $response->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }

    /** @test */
    public function user_can_logout()
    {
        $user = User::factory()->create();
        $token = auth()->login($user);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'User successfully signed out'
        ]);

        $response2 = $this->getJson('/api/auth/user-profile');
        $response2->assertStatus(401);
    }
}
