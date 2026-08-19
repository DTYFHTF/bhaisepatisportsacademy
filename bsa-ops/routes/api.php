<?php

use App\Http\Controllers\Api\DeviceAccessController;
use App\Http\Controllers\Api\PortalController;
use Illuminate\Support\Facades\Route;

/*
 * Device integration API — consumed by door controllers, turnstiles and
 * kiosks. Authenticated with per-device Sanctum tokens carrying the
 * `device:verify` ability (issued from the admin panel).
 */
Route::prefix('v1/device')
    ->middleware(['auth:sanctum', 'throttle:120,1'])
    ->group(function () {
        Route::post('/verify', [DeviceAccessController::class, 'verify']);
        Route::post('/heartbeat', [DeviceAccessController::class, 'heartbeat']);
        Route::post('/events', [DeviceAccessController::class, 'events']);
    });

/*
 * Member Portal API — GymMaster-style member self-service (see
 * docs/09-member-portal-api.md). Members authenticate with their own
 * Sanctum tokens carrying the `portal:member` ability.
 */
Route::prefix('v1/portal')->group(function () {
    Route::post('/login', [PortalController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::get('/profile', [PortalController::class, 'profile']);
        Route::get('/memberships', [PortalController::class, 'memberships']);
        Route::get('/balance', [PortalController::class, 'balance']);
        Route::get('/history', [PortalController::class, 'history']);
        Route::get('/visits', [PortalController::class, 'visits']);
        Route::get('/products', [PortalController::class, 'products']);
        Route::post('/products', [PortalController::class, 'purchase']);
    });
});
