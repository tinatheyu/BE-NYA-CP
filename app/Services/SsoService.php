<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SsoService
{
    public function getDataUser()
    {
        $resp = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('SSO_SERVICE'),
        ])->get(env('SSO_PORTAL_BASE_URL') . '/api/adminsite/users');

        if ($resp->failed()) {
            return response()->json([
                'status' => false,
                'message' => $resp->body(),
                'status_code' => $resp->status(),
            ], 404);
        }

        $responseBody = $resp->json();
        $data = $responseBody['data'] ?? [];

        return $data;
    }

    public function getDataUserById($user_id)
    {
        $resp = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('SSO_SERVICE'),
        ])->get(env('SSO_PORTAL_BASE_URL') . '/api/adminsite/users/' . $user_id);

        if ($resp->failed()) {
            return response()->json([
                'status' => false,
                'message' => $resp->body(),
                'status_code' => $resp->status(),
            ], 404);
        }

        $responseBody = $resp->json();
        $data = $responseBody['data'] ?? [];

        return $data;
    }

    public function getDataClassRoom($user_id = null)
    {
        $query = [];

        if (!empty($user_id)) {
            $query['user_id'] = $user_id;
        }

        $resp = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('SSO_SERVICE'),
        ])->get(env('SSO_PORTAL_BASE_URL') . '/api/adminsite/classrooms', $query);

        if ($resp->failed()) {
            return response()->json([
                'status' => false,
                'message' => $resp->body(),
                'status_code' => $resp->status(),
            ], 404);
        }

        $responseBody = $resp->json();
        $data = $responseBody['data'] ?? [];

        return $data;
    }

    public function getDataClassRoomById($classroom_id)
    {
        $resp = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('SSO_SERVICE'),
        ])->get(env('SSO_PORTAL_BASE_URL') . '/api/adminsite/classrooms/' . $classroom_id);

        if ($resp->failed()) {
            return response()->json([
                'status' => false,
                'message' => $resp->body(),
                'status_code' => $resp->status(),
            ], 404);
        }

        $responseBody = $resp->json();
        $data = $responseBody['data'] ?? [];

        return $data;
    }

    public function getDataProgramStudies($user_id = null)
    {
        $query = [];

        if (!empty($user_id)) {
            $query['user_id'] = $user_id;
        }

        $resp = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('SSO_SERVICE'),
        ])->get(
            env('SSO_PORTAL_BASE_URL') . '/api/adminsite/program-studies',
            $query
        );

        if ($resp->failed()) {
            return response()->json([
                'status' => false,
                'message' => $resp->body(),
                'status_code' => $resp->status(),
            ], $resp->status());
        }

        $responseBody = $resp->json();

        return $responseBody['data'] ?? null;
    }

    public function getDataProgramStudiesById($program_studies_id)
    {
        $resp = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('SSO_SERVICE'),
        ])->get(env('SSO_PORTAL_BASE_URL') . '/api/adminsite/program-studies/' . $program_studies_id);

        if ($resp->failed()) {
            return response()->json([
                'status' => false,
                'message' => $resp->body(),
                'status_code' => $resp->status(),
            ], 404);
        }

        $responseBody = $resp->json();
        $data = $responseBody['data'] ?? [];

        return $data;
    }
}
