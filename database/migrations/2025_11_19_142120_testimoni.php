<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('testimoni', function (Blueprint $table) {
            $table->bigIncrements('id');               
            $table->string('nama', 255);              
            $table->text('pesan');   
            $table->integer('rating');
            $table->integer('status_active')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimoni');
    }
};
