<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('tentang_kamis', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('telepon');
            $table->text('instagram');
            $table->text('alamat');
            $table->text('email');
            $table->text('deskripsi');
            $table->string('visi')->nullable();
            $table->text('misi')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tentang_kamis');
    }
};
