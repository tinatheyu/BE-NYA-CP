<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tentangkami extends Model
{
    use HasFactory;

    protected $table = 'tentang_kamis';

    protected $fillable = ['nama', 'email', 'telepon', 'instagram', 'alamat', 'sejarah', 'visi', 'misi', 'program', 'gambar', 'judul', 'deskripsi'];
}
