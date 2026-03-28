<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\RestockController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatController;
use App\Http\Controllers\TestimonialController;
use App\Http\Middleware\TrackingTokenAuth;
use Illuminate\Support\Facades\Route;

// Programs
Route::get('/programs', [ProgramController::class, 'index']);
Route::get('/programs/{slug}', [ProgramController::class, 'show']);

// Facilities
Route::get('/facilities', [FacilityController::class, 'index']);
Route::get('/facilities/{slug}', [FacilityController::class, 'show']);

// Schedule
Route::get('/schedule', [ScheduleController::class, 'index']);

// Testimonials
Route::get('/testimonials', [TestimonialController::class, 'index']);

// Kitchen
Route::get('/kitchen', [KitchenController::class, 'index']);
Route::get('/kitchen/{slug}', [KitchenController::class, 'show']);

// Site stats
Route::get('/stats', [StatController::class, 'index']);

// Services
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{slug}', [ServiceController::class, 'show']);

// Bookings
Route::post('/bookings', [BookingController::class, 'store']);
Route::get('/bookings/{ref}', [BookingController::class, 'show']);

// Products
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);

// Site settings (public, cached)
Route::get('/settings', [SettingsController::class, 'index']);

// OTP
Route::middleware('throttle:otp')->group(function () {
    Route::post('/otp/send', [OtpController::class, 'send']);
    Route::post('/otp/verify', [OtpController::class, 'verify']);
});

// Checkout
Route::middleware('throttle:checkout')->group(function () {
    Route::post('/checkout/place', [CheckoutController::class, 'place']);
});

// Payment callbacks
Route::get('/checkout/khalti/callback', [CheckoutController::class, 'khaltiCallback']);
Route::get('/checkout/esewa/callback', [CheckoutController::class, 'esewaCallback']);

// Order tracking
Route::middleware(TrackingTokenAuth::class)->group(function () {
    Route::get('/orders/track', [OrderController::class, 'show']);
});

// Public - order lookup by phone and/or order ID (no auth required)
Route::post('/orders/lookup', [OrderController::class, 'lookup']);

// Restock alerts
Route::middleware('throttle:restock')->post('/restock', [RestockController::class, 'store']);
