<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\UserProfileController;

// Public Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public Room Routes
Route::get('/rooms', [RoomController::class, 'index']);
Route::get('/rooms/{id}', [RoomController::class, 'show']);
Route::post('/rooms/check-availability', [RoomController::class, 'checkAvailability']);
Route::get('/rooms/{id}/availability-calendar', [RoomController::class, 'getAvailabilityCalendar']);

// Public Booking Routes
Route::post('/booking', [BookingController::class, 'store']);
Route::post('/booking/check-availability', [BookingController::class, 'getRoomAvailability']);

// Public Payment Routes
Route::post('/payments/create', [PaymentController::class, 'store']);

// Public Reviews (read-only)
Route::get('/rooms/{roomId}/reviews', [ReviewController::class, 'roomReviews']);

// Protected Routes (Authenticated Users)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // User Profile
    Route::get('/user/profile', [UserProfileController::class, 'show']);
    Route::put('/user/profile', [UserProfileController::class, 'update']);
    Route::post('/user/change-password', [UserProfileController::class, 'changePassword']);
    Route::get('/user/bookings-summary', [UserProfileController::class, 'bookingsSummary']);
    
    // User Bookings
    Route::get('/my_bookings', [BookingController::class, 'userBookings']);
    Route::get('/booking/{bookingId}', [BookingController::class, 'show']);
    Route::post('/booking/{booking_id}/cancel', [BookingController::class, 'cancel']);
    
    // User Payments
    Route::get('/my_payments', [PaymentController::class, 'userPayments']);
    Route::get('/payments/{paymentId}', [PaymentController::class, 'show']);
    Route::post('/payments/upload-proof', [PaymentController::class, 'uploadProof']);
    
    // User Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // Admin Routes
    Route::middleware('admin')->group(function () {
        // Dashboard
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);

        // Room Management
        Route::get('/admin/rooms', [AdminController::class, 'listRooms']);
        Route::post('/admin/rooms', [AdminController::class, 'createRoom']);
        Route::put('/admin/rooms/{id}', [AdminController::class, 'updateRoom']);
        Route::delete('/admin/rooms/{id}', [AdminController::class, 'deleteRoom']);

        // Booking Management
        Route::get('/admin/bookings', [AdminController::class, 'listBookings']);

        // User Management
        Route::get('/admin/users', [AdminController::class, 'listUsers']);

        // Payment Verification
        Route::get('/admin/payments/pending', [AdminController::class, 'pendingPayments']);
        Route::post('/payments/{payment_id}/verify', [PaymentController::class, 'verifyProof']);

        // Reports
        Route::get('/admin/reports', [AdminController::class, 'reports']);

        // Price Management
        Route::post('/admin/prices', [AdminController::class, 'updatePrices']);
    });
});

