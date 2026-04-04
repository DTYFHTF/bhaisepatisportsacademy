<?php

use App\Exceptions\InvalidOtpException;
use App\Exceptions\OutOfStockException;
use App\Exceptions\PaymentInitiationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->use([
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        $middleware->api(append: [
            \App\Http\Middleware\CamelCaseResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (OutOfStockException $e) {
            return response()->json([
                'error'   => 'OUT_OF_STOCK',
                'message' => 'This size is no longer available.',
            ], 409);
        });

        $exceptions->renderable(function (InvalidOtpException $e) {
            return response()->json([
                'error'   => 'INVALID_OTP',
                'message' => 'Invalid or expired code.',
            ], 422);
        });

        $exceptions->renderable(function (PaymentInitiationException $e) {
            return response()->json([
                'error'   => 'PAYMENT_ERROR',
                'message' => $e->getMessage(),
            ], 502);
        });

        $exceptions->renderable(function (ValidationException $e) {
            return response()->json([
                'error'  => 'VALIDATION_ERROR',
                'fields' => $e->errors(),
            ], 422);
        });
    })->create();
