<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SsoToken extends Model
{
    protected $table = 'sso_token';
    protected $fillable = ['user_id', 'role_id', 'original_token', 'tahun_ajaran', 'semester', 'revoked', 'expires_at'];
}
