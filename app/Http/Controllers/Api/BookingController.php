<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    // Simpan booking baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bookingId'   => 'required|string|max:255',
            'firstName'   => 'required|string|max:255',
            'lastName'    => 'required|string|max:255',
            'email'       => 'required|email|max:255',
            'phone'       => 'required|string|max:20',
            'roomType'    => 'required|string',
            'checkin'     => 'required|date',
            'checkout'    => 'required|date|after:checkin',
            'guests'      => 'required|integer|min:1|max:10',
            'nights'      => 'required|integer|min:1',
            'rate'        => 'required|numeric|min:0',
            'total'       => 'required|numeric|min:0',
            'specialRequests' => 'nullable|string',
            'userEmail'   => 'nullable|email'
        ]);

        try {
            // Cari user jika email ada
            $user = null;
            if (!empty($validated['userEmail'])) {
                $user = User::where('email', $validated['userEmail'])->first();
            }

            // Simpan booking
            $booking = Booking::create([
                'user_id'          => $user ? $user->id : null,
                'booking_id'       => $validated['bookingId'],
                'first_name'       => $validated['firstName'],
                'last_name'        => $validated['lastName'],
                'email'            => $validated['email'],
                'phone'            => $validated['phone'],
                'room_type'        => $validated['roomType'],
                'check_in'         => $validated['checkin'],
                'check_out'        => $validated['checkout'],
                'guests'           => $validated['guests'],
                'nights'           => $validated['nights'],
                'rate'             => $validated['rate'],
                'total'            => $validated['total'],
                'status'           => 'confirmed',
                'special_requests' => $validated['specialRequests'] ?? null
            ]);

            // Notifikasi (opsional)
            if ($user) {
                NotificationService::notifyBookingConfirmation($booking, $user);
            }

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'booking' => $booking
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating booking: ' . $e->getMessage()
            ], 500);
        }
    }

    // Booking user (auth)
    public function userBookings()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success'=>false,'message'=>'Unauthorized'], 401);
        }

        $bookings = Booking::where('user_id', $user->id)
            ->orderBy('created_at','desc')->get();

        return response()->json(['success'=>true,'bookings'=>$bookings]);
    }

    // Tampilkan booking spesifik
    public function show($bookingId)
    {
        $booking = Booking::where('booking_id',$bookingId)->first();
        if (!$booking) {
            return response()->json(['success'=>false,'message'=>'Booking not found'],404);
        }
        return response()->json(['success'=>true,'booking'=>$booking]);
    }
}
