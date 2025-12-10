<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BookingController;

// Public Routes - Serve new hotel booking frontend
Route::get("/", function () {
    return file_get_contents(public_path('index.html'));
});

Route::get("/index.html", function () {
    return file_get_contents(public_path('index.html'));
});

Route::get("/signin.html", function () {
    return file_get_contents(public_path('signin.html'));
});

Route::get("/signup.html", function () {
    return file_get_contents(public_path('signup.html'));
});

Route::get("/booking.html", function () {
    return file_get_contents(public_path('booking.html'));
});

// Admin Routes
route::get('/admin', [AdminController::class,'home']);
route::get('/admin/home', [AdminController::class, 'index'])->name('home');
route::get("/admin/create_room", [AdminController::class,'create_room']);
route::post("/admin/add_room", [AdminController::class,'add_room']);
route::get("/admin/view_room", [AdminController::class,'view_room']);

route::get("/room_delete/{id}", [AdminController::class,'room_delete']);

route::get("/room_update/{id}", [AdminController::class,'room_update']); 

route::get("/room_details/{id}", [HomeController::class,'room_details']);

route::post("/add_booking/{id}", [HomeController::class,'add_booking']);

route::get("/bookings", [AdminController::class,'bookings']);

route::get('/delete_booking/{id}', [AdminController::class,'delete_booking']);

route::get('/approve_book/{id}', [AdminController::class,'approve_book']);

route::get('/reject_book/{id}', [AdminController::class,'reject_book']);

// User booking history and payment
route::get('/my_bookings', [BookingController::class, 'index'])->name('my.bookings');
route::get('/booking/{id}/pay', [BookingController::class, 'showPayment'])->name('booking.pay');
route::post('/booking/{id}/pay', [BookingController::class, 'processPayment'])->name('booking.pay.process');
