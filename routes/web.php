<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;

// Public Routes - Serve hotel booking frontend
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
Route::get('/admin', [AdminController::class,'home']);
Route::get('/admin/home', [AdminController::class, 'index'])->name('home');
Route::get("/admin/create_room", [AdminController::class,'create_room']);
Route::post("/admin/add_room", [AdminController::class,'add_room']);
Route::get("/admin/view_room", [AdminController::class,'view_room']);
Route::get("/room_delete/{id}", [AdminController::class,'room_delete']);
Route::get("/room_update/{id}", [AdminController::class,'room_update']); 
Route::get("/room_details/{id}", [HomeController::class,'room_details']);
Route::get("/bookings", [AdminController::class,'bookings']);
Route::get('/delete_booking/{id}', [AdminController::class,'delete_booking']);
Route::get('/approve_book/{id}', [AdminController::class,'approve_book']);
Route::get('/reject_book/{id}', [AdminController::class,'reject_book']);
