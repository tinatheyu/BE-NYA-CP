<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SsoToken;

class CheckSsoToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika middleware dimatikan melalui ENV → bypass
        if (!env('SSO_MIDDLEWARE_CHECK_ENABLED', true)) {
            return $next($request);
        }
        // 1. Ambil token dari header Authorization
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json([
                'status'  => false,
                'message' => 'Authorization token missing'
            ], 401);
        }

        $token = substr($header, 7);

        // 2. Cek token di DB
        $record = SsoToken::where('original_token', $token)->first();

        if (!$record) {
            return response()->json([
                'status'  => false,
                'message' => 'Token not found'
            ], 401);
        }

        // 3. Cek revoked
        if ($record->revoked) {
            return response()->json([
                'status'  => false,
                'message' => 'Token revoked'
            ], 401);
        }

        // 4. Cek expired
        if ($record->expires_at && now()->greaterThan($record->expires_at)) {
            return response()->json([
                'status'  => false,
                'message' => 'Token expired'
            ], 401);
        }

        // 5. Cek role ID
        if (!empty($roles)) {
            if (!in_array($record->role_id, $roles)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthorized role (required role: ' . implode(',', $roles) . ')'
                ], 403);
            }
        }

        // Simpan ke request (kalau controller butuh)
        $request->merge([
            'sso_user_id' => $record->user_id,
            'sso_role_id' => $record->role_id,
            'sso_original_token' => $record->original_token,
            'sso_tahun_ajaran' => $record->tahun_ajaran,
            'sso_semester' => $record->semester,
        ]);

        return $next($request);
    }
}
