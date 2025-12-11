<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\NotificationController;

// API Routes - untuk insert booking via JSON
Route::post('/booking', [BookingController::class, 'store']);

// Public Payment Routes
Route::post('/payments/create', [PaymentController::class, 'store']);

// Optional: user booking history via API
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::get('/my_bookings', [BookingController::class, 'userBookings']);
    Route::get('/booking/{bookingId}', [BookingController::class, 'show']);
    
    // Protected Payment Routes
    Route::get('/my_payments', [PaymentController::class, 'userPayments']);
    
    // Protected Notification Routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
});
