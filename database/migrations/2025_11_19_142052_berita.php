<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berita', function (Blueprint $table) {
            $table->id('id_berita');
            $table->string('judul');
            $table->string('slug')->unique();
            $table->longText('isi');
            $table->text('deskripsi')->nullable();
            $table->enum('kategori', [
                'Pengumuman',
                'Kegiatan Sekolah',
                'Prestasi',
                'Akademik',
                'Lomba',
                'Kunjungan / Study Tour',
                'Berita Umum'
            ])->default('Berita Umum');
            $table->string('media');
            $table->integer('status')->default(0);
            $table->date('publish_date')->nullable();
            $table->timestamps();   
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berita');
    }
};
