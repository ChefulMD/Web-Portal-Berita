<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    /**
     * Uji skenario di mana pengguna dengan role 'admin' berhasil login.
     *
     * @return void
     */
    public function test_admin_user_can_login_successfully()
    {
        
        $adminPassword = 'adminsecret';
        $admin = User::factory()->create([
            'email' => 'admin@portal.com',
            'password' => bcrypt($adminPassword), 
            'role' => 'admin', 
        ]);

        $credentials = [
            'email' => 'admin@portal.com',
            'password' => $adminPassword,
        ];
    
        $response = $this->postJson('/api/auth/login', $credentials);
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
            'user' => [
                'id',
                'name',
                'email',
                'role', 
            ]
        ]);
        
        
        $response->assertJsonFragment([
            'email' => 'admin@portal.com',
            'role' => 'admin',
        ]);

        $this->assertIsString($response->json('access_token'));
        $this->assertNotEmpty($response->json('access_token'));
    }

    public function test_admin_login_fails_with_wrong_password()
    {
        
        User::factory()->create([
            'email' => 'admin@portal.com',
            'password' => bcrypt('correctpassword'), 
            'role' => 'admin', 
        ]);

        $invalidCredentials = [
            'email' => 'admin@portal.com',
            'password' => 'wrongpassword', 
        ];

        
        $response = $this->postJson('/api/auth/login', $invalidCredentials);
        $response->assertStatus(401);

        $response->assertJson([
            'error' => 'Unauthorized' 
        ]);
    }

    public function test_admin_can_access_admin_dashboard_stats()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = auth('api')->login($admin); 
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/dashboard/stats');

       
        $response->assertStatus(200);
        $response->assertJsonStructure(['total_users', 'total_berita']); 
    }
    
}
