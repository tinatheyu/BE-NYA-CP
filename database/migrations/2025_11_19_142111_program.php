<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program', function (Blueprint $table) {
            $table->bigIncrements('id');               
            $table->string('nama', 255);              
            $table->text('deskripsi');    
            $table->string('media'); 
            $table->date('tanggal'); 
            $table->enum('tipe_media', ['gambar','vidio'])->default('gambar');     
            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('program');
    }
};
