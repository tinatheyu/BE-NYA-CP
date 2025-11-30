<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class berita extends Model
{
    protected $table = 'berita';
    protected $primaryKey = 'id_berita';
    public $incrementing = true; 
    protected $keyType = 'int';  
    protected $fillable = ['judul', 'slug', 'isi', 'deskripsi', 'kategori',  'status','media', 'publish_date' ];

 
};
