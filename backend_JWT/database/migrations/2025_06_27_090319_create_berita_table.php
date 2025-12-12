<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('berita', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Judul berita
            $table->string('slug')->unique(); // Slug untuk URL
            $table->longText('content'); // Isi berita, pakai longText untuk konten panjang
            $table->text('excerpt')->nullable(); // Ringkasan singkat
            $table->string('image')->nullable(); // Path/URL gambar utama
            $table->unsignedBigInteger('category_id')->nullable(); // Foreign Key ke tabel categories
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null'); // Relasi ke kategori

            $table->unsignedBigInteger('user_id'); // Foreign Key ke tabel users (penulis)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Relasi ke user

            $table->enum('status', ['draft', 'published', 'archived'])->default('draft'); // Status berita
            $table->timestamp('published_at')->nullable(); // Tanggal publikasi
            $table->integer('views_count')->default(0); // Jumlah tampilan
            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berita');
    }
};