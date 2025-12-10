<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class testimoni extends Model
{
    use HasFactory;
    protected $table = 'testimoni';

    protected $fillable = [
        'nama',
        'pesan',
        'rating',
        'status_active'
    ];
}
