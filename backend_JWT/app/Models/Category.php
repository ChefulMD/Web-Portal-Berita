<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // Tambahkan ini jika Anda ingin menggunakan Str::slug() di observer atau mutator

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * Get the berita (news articles) for the category.
     * Mendefinisikan hubungan satu-ke-banyak: Satu kategori bisa memiliki banyak berita.
     */
    public function berita()
    {
        return $this->hasMany(Berita::class);
    }

    /*
     * Contoh Mutator/Accessor (Opsional tapi berguna):
     * Jika Anda ingin memastikan slug selalu dibuat secara otomatis saat nama diatur.
     * Ini bisa juga dilakukan di Controller saat menyimpan data atau di Seeder.
     *
     * public function setNameAttribute($value)
     * {
     * $this->attributes['name'] = $value;
     * $this->attributes['slug'] = Str::slug($value);
     * }
     */

    /*
     * Contoh Query Scope (Opsional):
     * Untuk memudahkan query umum, misalnya mendapatkan kategori berdasarkan slug.
     *
     * public function scopeWhereSlug($query, $slug)
     * {
     * return $query->where('slug', $slug);
     * }
     */
}