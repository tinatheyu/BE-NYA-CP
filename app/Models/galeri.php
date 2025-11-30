<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class galeri extends Model
{
    use HasFactory;

    protected $table = 'galeri';

    protected $primaryKey = 'id';

    protected $fillable = [
        'judul',
        'deskripsi',
        'media',
        'tipe_media',
        'tanggal'
    ];

    public $timestamps = true;
}
