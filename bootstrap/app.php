<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckSsoToken;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'sso' => CheckSsoToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $exception, $request) {
            return response()->json([
                'code'    => 422,
                'message' => 'Validation Error',
                'errors'    => $exception->validator->errors(),
            ], 422);
        });

        $exceptions->render(function (Throwable $exception, $request) {
            // Route tidak ditemukan
            if ($exception instanceof NotFoundHttpException) {
                return response()->json([
                    'code'    => 403,
                    'message' => 'Forbidden: route tidak ditemukan.',
                    'data'    => null,
                ], 403);
            }

            // Tidak punya hak akses / permission
            if ($exception instanceof UnauthorizedException) {
                return response()->json([
                    'code'    => 403,
                    'message' => 'Forbidden: Anda tidak memiliki izin untuk mengakses route ini.',
                    'data'    => null,
                ], 403);
            }

            // Default: error lainnya
            return response()->json([
                'code'    => 500,
                'message' => 'Internal Server Error',
                'data'    => null
            ], 500);
        });
    })->create();
