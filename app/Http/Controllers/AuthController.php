<?php

namespace App\Http\Controllers;

use App\Models\SsoToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Firebase\JWT\JWT;
use Carbon\Carbon;
use App\Services\SsoService;
use App\Http\Requests\Auth\LoginRules;
use Exception;

class AuthController extends Controller
{
    protected $ssoService;
    public function __construct(SsoService $ssoService)
    {
        $this->ssoService = $ssoService;
    }
    public function callback(Request $r)
    {
        try {
            $token = $r->query('token');
            $ts    = $r->query('ts');
            $sig   = $r->query('sig');

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

            if ($resp->failed()) {
                return response()->json([
                    'status' => false,
                    'message' => $resp->body(),
                    'status_code' => $resp->status(),
                ], 401);
            }

            $user = $resp->json('user');
            $original_token = $resp->json('token');
            $expires_at = $resp->json('expires_at');

            SsoToken::create([
                'user_id'        => $user['id'],
                'role_id'        => $user['role']['id'],
                'tahun_ajaran'   => $user['config']['academic_year'],
                'semester'       => $user['config']['semester'],
                'original_token' => $original_token,
                'expires_at'     => $expires_at,
                'revoked'        => false,
            ]);

            // Generate JWT local token FE
            $payload = [
                'user' => $user,
                'original_token' => $original_token,
                'portal_token' => $resp->json('portal_token'),
                'iat' => time(),
                'exp' => $expires_at,
            ];

            $jwtSecret = env('JWT_SECRET');
            $localToken = JWT::encode($payload, $jwtSecret, 'HS256');

            $url = env('APP_URL_FE') . '/sso/callback?token=' . $localToken;

            return redirect()->away($url);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(loginRules $request)
    {
        try {
            $request->validated();

            $resp = Http::post(env('SSO_PORTAL_BASE_URL') . '/api/login', $request->all());

            if ($resp->failed()) {
                return response()->json([
                    'status' => false,
                    'message' => $resp->body(),
                    'status_code' => $resp->status(),
                ], 401);
            }

            $response = $resp->json('data');

            $user           = $response['user'];
            $original_token = $response['original_token'];
            $expires_at     = Carbon::parse($response['expires_at'])->toDateTimeString();

            SsoToken::create([
                'user_id'        => $user['id'],
                'role_id'        => $user['role']['id'],
                'tahun_ajaran'   => $user['config']['academic_year'],
                'semester'       => $user['config']['semester'],
                'original_token' => $original_token,
                'expires_at'     => $expires_at,
                'revoked'        => false,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $original_token = $request->original_token;

        if (!$original_token) {
            return response()->json([
                'status' => false,
                'message' => 'Missing local token'
            ], 400);
        }

        // 1. VALIDATE in Portal
        $check = Http::post(env('SSO_PORTAL_BASE_URL') . '/api/check-token', [
            'token' => $original_token
        ]);

        if (!$check->ok() || !$check->json('status')) {
            return response()->json([
                'status' => false,
                'message' => 'Portal check-token failed',
            ], 400);
        }

        // 2. GLOBAL LOGOUT
        $resp = Http::post(env('SSO_PORTAL_BASE_URL') . '/api/logout-global', [
            'token' => $original_token
        ]);

        if ($resp->failed()) {
            return response()->json([
                'status' => false,
                'message' => 'Portal global logout failed',
            ], 400);
        }

        // 3. UPDATE ROVOKE SSO TOKEN
        $ut = SsoToken::where('original_token', $original_token)->first();

        if (!$ut) {
            return response()->json([
                'status' => false,
                'message' => 'Token not found (invalid or already revoked)',
                'token_received' => $original_token
            ], 404);
        }

        if ($ut->revoked) {
            return response()->json([
                'status' => false,
                'message' => 'Token is already revoked'
            ], 400);
        }

        $ut->revoked = true;
        $ut->save();

        return response()->json([
            'status' => true,
            'message' => 'Logout successful',
            'redirect' => env('SSO_PORTAL_BASE_URL') . '/'
        ]);
    }

    public function testService(Request $request)
    {
        $userId = $request->sso_user_id;
        $roleId = $request->sso_role_id;
        $tahunAjaran = $request->sso_tahun_ajaran;
        $semester = $request->sso_semester;

        $dataClassroom = $this->ssoService->getDataClassRoom($userId);
        $dataUser = $this->ssoService->getDataUserById($userId);

        return response()->json([
            "status" => true,
            "message" => "Success",
            "data" => $dataUser
        ]);
    }
}
