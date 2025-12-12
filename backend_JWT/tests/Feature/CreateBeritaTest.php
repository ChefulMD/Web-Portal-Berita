<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Berita; 
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateBeritaTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;
    protected function createCategory(): Category
    {
        return Category::create([
            'name' => 'Kategori Create',
            'slug' => 'kategori-create',
            'description' => 'Deskripsi Kategori Create',
        ]);
    }

    protected function getAuthToken(string $role): array
    {
        $user = User::create([
            'name' => 'Tester ' . $role,
            'email' => $role . '_' . Str::random(5) . '@test.com', 
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
        
        return [
            'token' => auth('api')->login($user),
            'user' => $user,
        ];
    }

    public function test_regular_user_can_create_news_successfully()
    {
        Storage::fake('public'); 
        $category = $this->createCategory(); 
        $auth = $this->getAuthToken('user'); 

        $title = 'Judul Berita User Spesial';
        $content = 'Ini adalah konten berita yang cukup panjang untuk testing excerpt otomatis.';
        $image = UploadedFile::fake()->image('test_news.jpg');

        $beritaData = [
            'title' => $title,
            'content' => $content,
            'category_id' => $category->id,
            'status' => 'draft',
            'image' => $image,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $auth['token'],
        ])->postJson('/api/berita', $beritaData);

        $response->assertStatus(201); 

        $response->assertJsonFragment([
            'message' => 'Berita berhasil dibuat!',
            'title' => $title,
        ]);

        $this->assertDatabaseHas('berita', [
            'user_id' => $auth['user']->id,
            'title' => $title,
            'slug' => Str::slug($title), 
            'status' => 'draft',
            'excerpt' => Str::limit(strip_tags($content), 150), 
        ]);

        $berita = Berita::where('title', $title)->first();
        $this->assertNotNull($berita->image);
        Storage::disk('public')->assertExists($berita->image);
    }

    public function test_admin_is_forbidden_to_create_news()
    {
        $category = $this->createCategory(); 
        $auth = $this->getAuthToken('admin'); 

        $beritaData = [
            'title' => 'Judul Admin Ilegal',
            'content' => 'Konten admin.',
            'category_id' => $category->id,
            'status' => 'published',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $auth['token'],
        ])->postJson('/api/berita', $beritaData);

        $response->assertStatus(403); 

        $this->assertDatabaseMissing('berita', [
            'title' => 'Judul Admin Ilegal',
        ]);
    }

    public function test_create_news_fails_validation_missing_fields()
    {
        $auth = $this->getAuthToken('user'); 

        $invalidData = [
            'status' => 'published', 
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $auth['token'],
        ])->postJson('/api/berita', $invalidData);

        $response->assertStatus(422); 

        $response->assertJsonValidationErrors(['title', 'content', 'category_id']);
    }

    public function test_create_news_fails_validation_invalid_category()
    {
        $auth = $this->getAuthToken('user'); 

        $invalidData = [
            'title' => 'Judul Valid',
            'content' => 'Konten Valid',
            'category_id' => 99999, 
            'status' => 'draft',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $auth['token'],
        ])->postJson('/api/berita', $invalidData);

        // ASSERT
        $response->assertStatus(422); 
        $response->assertJsonValidationErrors(['category_id']);
    }

    public function test_unauthenticated_user_cannot_create_news()
    {
        $response = $this->postJson('/api/berita', [
            'title' => 'Tanpa Token',
        ]);
        $response->assertStatus(401); 
    }
}
