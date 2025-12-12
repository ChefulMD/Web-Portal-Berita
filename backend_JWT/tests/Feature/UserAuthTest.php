<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserAuthTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Uji skenario di mana pengguna dengan role 'user' berhasil login
     *
     * @return void
     */
    public function test_regular_user_can_login_successfully()
    {
        
        $userPassword = 'usersecret';
        $user = User::factory()->create([
            'email' => 'regular@user.com',
            'password' => bcrypt($userPassword), 
            'role' => 'user', 
        ]);

        $credentials = [
            'email' => 'regular@user.com',
            'password' => $userPassword, 
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
            'email' => 'regular@user.com',
            'role' => 'user',
        ]);

        
        $this->assertIsString($response->json('access_token'));
        $this->assertNotEmpty($response->json('access_token'));
    }
    
    /**
     * Uji skenario di mana pengguna dengan role 'user' gagal login karena password salah
     *
     * @return void
     */
    public function test_regular_user_login_fails_with_wrong_password()
    {
        
        User::factory()->create([
            'email' => 'regular@user.com',
            'password' => bcrypt('correctpassword'), 
            'role' => 'user', 
        ]);

        $invalidCredentials = [
            'email' => 'regular@user.com',
            'password' => 'wrongpassword', 
        ];

        
        $response = $this->postJson('/api/auth/login', $invalidCredentials);
        $response->assertStatus(401);
        
        $response->assertJson([
            'error' => 'Unauthorized' 
        ]);
    }
}
