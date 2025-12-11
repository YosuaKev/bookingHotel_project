<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingController;

// API Routes - untuk insert booking via JSON
Route::post('/booking', [BookingController::class, 'store']);

// Optional: user booking history via API
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my_bookings', [BookingController::class, 'userBookings']);
    Route::get('/booking/{bookingId}', [BookingController::class, 'show']);
});
