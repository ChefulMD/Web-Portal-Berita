<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Berita; 
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BeritaUpdateTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected function createCategory(): Category
    {
        return Category::create([
            'name' => 'Kategori Test Update',
            'slug' => 'kategori-test-update',
            'description' => 'Deskripsi Kategori',
        ]);
    }

    protected function createUser(string $role): User
    {
        return User::create([
            'name' => 'Tester ' . $role,
            'email' => $role . '_' . Str::random(5) . '@test.com', // Email unik
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }

    protected function createBerita(User $user, Category $category): Berita
    {
        $title = 'Berita Awal ' . Str::random(5);
        return Berita::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => 'Konten awal berita ini.',
            'excerpt' => 'Konten awal...',
            'status' => 'draft',
            'image' => null,
        ]);
    }

    
    public function test_author_can_update_their_own_berita()
    {
        Storage::fake('public');
        $category = $this->createCategory();
        $author = $this->createUser('user');
        $token = auth('api')->login($author);
        
        $berita = $this->createBerita($author, $category);
        
        $updateData = [
            'title' => 'Judul Berita Telah Diupdate',
            'content' => 'Konten yang sudah diperbarui oleh penulis.',
            'category_id' => $category->id,
            'status' => 'published',
            'image' => UploadedFile::fake()->image('new_image.jpg'),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/berita/' . $berita->id, $updateData);

        $response->assertStatus(200); 
        
        $response->assertJsonFragment([
            'message' => 'Berita berhasil diperbarui!',
            'title' => 'Judul Berita Telah Diupdate',
            'status' => 'published',
        ]);

        $this->assertDatabaseHas('berita', [
            'id' => $berita->id,
            'title' => 'Judul Berita Telah Diupdate',
            'slug' => 'judul-berita-telah-diupdate', 
            'status' => 'published',
            'user_id' => $author->id, 
        ]);

        $beritaUpdated = Berita::find($berita->id);
        $this->assertNotNull($beritaUpdated->image);
        Storage::disk('public')->assertExists($beritaUpdated->image);
    }

    public function test_admin_can_update_other_users_berita()
    {
        $category = $this->createCategory();
        $author = $this->createUser('user');
        $berita = $this->createBerita($author, $category);

        $admin = $this->createUser('admin');
        $token = auth('api')->login($admin);

        $updateData = [
            'title' => 'Judul Diedit Admin',
            'content' => 'Admin mengubah konten ini.',
            'category_id' => $category->id,
            'status' => 'archived',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/berita/' . $berita->id, $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('berita', [
            'id' => $berita->id,
            'title' => 'Judul Diedit Admin',
            'status' => 'archived',
            'user_id' => $author->id, 
        ]);
    }

    public function test_user_cannot_update_others_berita()
    {
        $category = $this->createCategory();
        $author = $this->createUser('user');
        $berita = $this->createBerita($author, $category);

        $attacker = $this->createUser('user'); 
        $token = auth('api')->login($attacker);

        $updateData = [
            'title' => 'Judul Hack',
            'content' => 'Hacked content.',
            'category_id' => $category->id,
            'status' => 'published',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/berita/' . $berita->id, $updateData);

        $response->assertStatus(403);

        $this->assertDatabaseHas('berita', [
            'id' => $berita->id,
            'title' => $berita->title, 
        ]);
    }

    public function test_update_fails_validation_missing_fields()
    {
        $category = $this->createCategory();
        $author = $this->createUser('user');
        $token = auth('api')->login($author);
        $berita = $this->createBerita($author, $category);

        $invalidData = [
            'category_id' => $category->id,
            'status' => 'published',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/berita/' . $berita->id, $invalidData);

        $response->assertStatus(422); 
        $response->assertJsonValidationErrors(['title', 'content']);
    }

    public function test_update_fails_validation_duplicate_title()
    {
        $category = $this->createCategory();
        $author = $this->createUser('user');
        $token = auth('api')->login($author);

        $beritaA = $this->createBerita($author, $category);
        $beritaA->update(['title' => 'Judul Unik']); // Set judul eksisting

        $beritaB = $this->createBerita($author, $category);

        $invalidData = [
            'title' => 'Judul Unik',
            'content' => 'Konten.',
            'category_id' => $category->id,
            'status' => 'published',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/berita/' . $beritaB->id, $invalidData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
    }

    public function test_update_fails_not_found()
    {
        $author = $this->createUser('user');
        $token = auth('api')->login($author);
        $category = $this->createCategory();

        $updateData = [
            'title' => 'Judul',
            'content' => 'Konten',
            'category_id' => $category->id,
            'status' => 'draft',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/berita/99999', $updateData); // ID Ngawur

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Berita tidak ditemukan.']);
    }

    public function test_author_can_update_a_draft_status_berita()
    {
        $category = $this->createCategory();
        $author = $this->createUser('user');
        $token = auth('api')->login($author);

        $berita = $this->createBerita($author, $category); 

        $updateData = [
            'title' => 'Judul Berita Baru DARI DRAFT',
            'content' => 'Konten yang diperbarui dari status draft.',
            'category_id' => $category->id,
            'status' => 'published', 
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/berita/' . $berita->id, $updateData);

        $response->assertStatus(200);
 
        $this->assertDatabaseHas('berita', [
            'id' => $berita->id,
            'title' => 'Judul Berita Baru DARI DRAFT',
            'status' => 'published', // Verifikasi perubahan status
            'user_id' => $author->id,
        ]);
    }

    public function test_update_fails_if_berita_status_is_draft_403()
    {
        $category = $this->createCategory();
        $author = $this->createUser('user');
        $token = auth('api')->login($author);

        $beritaDraft = $this->createBerita($author, $category); 
        $this->assertEquals('draft', $beritaDraft->status);

        $updateData = [
            'title' => 'Coba Edit Draft',
            'content' => 'Konten yang seharusnya gagal diperbarui.',
            'category_id' => $category->id,
            'status' => 'published', 
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/berita/' . $beritaDraft->id, $updateData);

        $response->assertStatus(403); 

        $this->assertDatabaseHas('berita', [
            'id' => $beritaDraft->id,
            'title' => $beritaDraft->title, 
            'status' => 'draft', 
        ]);
    }
}
