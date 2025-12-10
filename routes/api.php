<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PaymentController;

// Public Auth Routes
Route::post('/users/register', [AuthController::class, 'register']);
Route::post('/users/login', [AuthController::class, 'login']);
Route::post('/auth/google', [AuthController::class, 'googleLogin']);
Route::post('/auth/microsoft', [AuthController::class, 'microsoftLogin']);

// Public Booking Routes
Route::post('/bookings/create', [BookingController::class, 'store']);
Route::get('/bookings/{bookingId}', [BookingController::class, 'show']);

// Public Payment Routes
Route::post('/payments/create', [PaymentController::class, 'store']);
Route::get('/payments/{paymentId}', [PaymentController::class, 'show']);
Route::get('/payments/booking/{bookingId}', [PaymentController::class, 'getByBooking']);

// Protected Routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::get('/user/profile', [AuthController::class, 'user']);
    Route::post('/user/logout', [AuthController::class, 'logout']);
    
    // Protected Booking Routes
    Route::get('/my-bookings', [BookingController::class, 'userBookings']);
    
    // Protected Payment Routes
    Route::get('/my-payments', [PaymentController::class, 'userPayments']);
});
