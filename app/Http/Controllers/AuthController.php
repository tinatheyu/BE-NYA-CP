<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JwtAuth\Facades\JwtAuth;
use Tymon\JwtAuth\Exceptions\JwtException;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        try {
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $token = JwtAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil didaftarkan',
                'data'    => $user,
                'token'   => $token,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendaftarkan user: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        try {
            $credentials = $request->only(['email', 'password']);

            if (!$token = JwtAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau password salah',
                ], 401);
            }

            $user = auth()->user();

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data'    => $user,
                'token'   => $token,
            ], 200);
        } catch (JwtException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate token: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function logout()
    {
        try {
            JwtAuth::invalidate(JwtAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil',
            ], 200);
        } catch (JwtException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal logout: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function refresh()
    {
        try {
            $newToken = JwtAuth::refresh(JwtAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Token berhasil diperbarui',
                'token'   => $newToken,
            ], 200);
        } catch (JwtException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal refresh token: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function me()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data user berhasil diambil',
                'data'    => $user,
            ], 200);
        } catch (JwtException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data user: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function destroy($id)
{
    try {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus',
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal menghapus user: ' . $e->getMessage(),
        ], 500);
    }
}

public function callback(Request $request){
    try {
        $token = $request->query('token');
        $ts    = $request->query('ts');
        $sig   = $request->query('sig');

        if (!$token || !$ts || !$sig) {
            return response()->json([
                'status' => false,
                'message' => 'Missing required parameters',
            ], 400);
        }

        if (abs(now()->timestamp - (int)$ts) > 120) {
            return response()->json([
                'status' => false,
                'message' => 'Expired callback timestamp',
            ], 400);
        }

        $resp = Http::post(env('SSO_PORTAL_BASE_URL') . '/api/validate-token', [
            'token'     => $token,
            'app_key'   => env('SSO_CLIENT_APP_KEY'),
            'timestamp' => $ts,
            'signature' => $sig,
        ]);
        return $resp;
        if ($resp->failed()) {
            return response()->json([
                'status' => false,
                'message' => $resp->body(), 
                'status_code' => $resp->status(),
            ], 401);
        }

        $user = $resp->json('user');

        $localUser = User::firstOrCreate(
            ['email' => $user['email']],
            [
                'name' => $user['name'],
                'role' => $user['role']['id'] ?? 'siswa',
                'password' => null,
            ]
        );

        $payload = [
            'id' => $localUser->id,
            'email' => $localUser->email,
            'role' => $localUser->role,
            'iat' => time(),
            'exp' => time() + 3600,
        ];

        $jwtSecret = env('JWT_SECRET');
        $localToken = JWT::encode($payload, $jwtSecret, 'HS256');


        $url = env('APP_URL_FE') . '/sso/callback?token=' . $localToken;

        return redirect()->away($url);
    } catch (Exception $e) {
        return response()->json([
            'status' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}


}

