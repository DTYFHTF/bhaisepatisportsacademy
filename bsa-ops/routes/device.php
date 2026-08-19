<?php

use App\Http\Controllers\Api\AdmsController;
use Illuminate\Support\Facades\Route;

/*
 * ZKTeco PUSH / ADMS endpoints — see docs/10-zkteco-integration.md.
 *
 * These paths are dictated by the hardware: the firmware takes only a
 * server address and port, then appends /iclock/... itself. That is why
 * they live at the domain root rather than under /api, and outside the
 * web group (no session, no CSRF — the device cannot send a token).
 *
 * Identity is the serial number, validated against registered devices
 * inside the controller.
 */
Route::get('/iclock/cdata', [AdmsController::class, 'handshake']);
Route::post('/iclock/cdata', [AdmsController::class, 'push']);
Route::get('/iclock/getrequest', [AdmsController::class, 'getRequest']);
Route::post('/iclock/devicecmd', [AdmsController::class, 'deviceCmd']);
