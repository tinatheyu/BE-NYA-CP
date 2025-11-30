
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('galeri', function (Blueprint $table) {
            $table->bigIncrements('id');               
            $table->string('judul', 255);              
            $table->text('deskripsi')->nullable();    
            $table->string('media');             
            $table->enum('tipe_media',['gambar','vidio'])->default('gambar');                  
            $table->date('tanggal')    ;              
            $table->timestamps();                     
        });
    }

    public function down()
    {
        Schema::dropIfExists('galeri');
    }
};
