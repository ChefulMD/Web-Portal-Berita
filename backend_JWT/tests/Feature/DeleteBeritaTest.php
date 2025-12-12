<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Berita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class DeleteBeritaTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected function createCategory(): Category
    {
        return Category::create([
            'name' => 'Kategori Hapus',
            'slug' => 'kategori-hapus',
            'description' => 'Deskripsi Kategori',
        ]);
    }

    protected function createUser(string $role): User
    {
        return User::create([
            'name' => 'Tester ' . $role,
            'email' => $role . '_' . Str::random(5) . '@test.com',
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }

    protected function createBerita(User $user, Category $category, $withImage = false): Berita
    {
        $imagePath = null;
        if ($withImage) {
            $file = UploadedFile::fake()->image('news.jpg');
            $imagePath = $file->store('news_images', 'public');
        }

        $title = 'Berita Hapus ' . Str::random(5);
        return Berita::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => 'Konten berita yang akan dihapus.',
            'excerpt' => 'Konten...',
            'status' => 'draft',
            'image' => $imagePath,
        ]);
    }

    public function test_author_can_delete_their_own_berita()
    {
        Storage::fake('public');
        $category = $this->createCategory();
        $author = $this->createUser('user');
        $token = auth('api')->login($author);

        $berita = $this->createBerita($author, $category, true);
        $imagePath = $berita->image;

        Storage::disk('public')->assertExists($imagePath);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/berita/' . $berita->id);

        $response->assertStatus(200); 
        $response->assertJson(['message' => 'Berita berhasil dihapus.']);

        $this->assertDatabaseMissing('berita', [
            'id' => $berita->id,
        ]);

        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_admin_can_delete_any_berita()
    {
        $category = $this->createCategory();
        $author = $this->createUser('user'); 
        $berita = $this->createBerita($author, $category); 

        $admin = $this->createUser('admin'); 
        $token = auth('api')->login($admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/berita/' . $berita->id);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('berita', [
            'id' => $berita->id,
        ]);
    }

    public function test_user_cannot_delete_others_berita()
    {
        $category = $this->createCategory();
        $author = $this->createUser('user');
        $berita = $this->createBerita($author, $category);

        $attacker = $this->createUser('user');
        $token = auth('api')->login($attacker);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/berita/' . $berita->id);

        $response->assertStatus(403); 
        $this->assertDatabaseHas('berita', [
            'id' => $berita->id,
        ]);
    }

    public function test_delete_fails_not_found_404()
    {
        $user = $this->createUser('user');
        $token = auth('api')->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/berita/99999'); 

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Berita tidak ditemukan.']);
    }

    public function test_unauthenticated_cannot_delete()
    {
        $response = $this->deleteJson('/api/berita/1');
        $response->assertStatus(401); // Unauthorized
    }
}
