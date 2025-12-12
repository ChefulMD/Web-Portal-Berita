<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo
use Illuminate\Database\Eloquent\Casts\Attribute; // Untuk Laravel 9+ Accessor/Mutator baru
use Illuminate\Support\Str; // Untuk slug, jika ingin diatur via mutator

class Berita extends Model
{
    use HasFactory;

    // Nama tabel jika berbeda dari konvensi Laravel (plural dari 'Berita' adalah 'Beritas',
    // tapi kita pakai 'berita' di migrasi, jadi lebih baik eksplisit)
    protected $table = 'berita';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'image',
        'category_id',
        'user_id', // Penting agar user_id bisa diisi
        'status',
        'published_at',
        'views_count', // Jika Anda ingin menginisialisasi ini
    ];

    /**
     * The attributes that should be cast.
     * Laravel akan otomatis mengubah tipe data ini.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_at' => 'datetime', // Mengubah string tanggal jadi objek Carbon (tanggal/waktu)
    ];

    /**
     * Get the category that owns the Berita.
     * Mendefinisikan hubungan banyak-ke-satu: Satu berita memiliki satu kategori.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }


    /**
     * Get the user (author) that owns the Berita.
     * Mendefinisikan hubungan banyak-ke-satu: Satu berita ditulis oleh satu user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
     * Contoh Attribute Cast (Laravel 9+): slug (Opsional, jika ingin otomatis)
     * Ini akan membuat slug secara otomatis saat 'title' diatur
     *
     * protected function slug(): Attribute
     * {
     * return Attribute::make(
     * set: fn (string $value) => Str::slug($this->title),
     * );
     * }
     *
     * Catatan: Jika Anda menggunakan ini, pastikan untuk menghapus 'slug' dari $fillable
     * dan atur 'title' sebelum menyimpan. Cara ini lebih kompleks, dan mengelola slug di controller/seeder
     * mungkin lebih sederhana untuk pemula.
     */

    /*
     * Contoh Accessor (Laravel <9 atau gaya lama): getPublishedAtAttribute
     * public function getPublishedAtAttribute($value)
     * {
     * return $value ? \Carbon\Carbon::parse($value)->format('d F Y') : null;
     * }
     * Ini akan memformat tanggal publikasi saat diakses
     */
}