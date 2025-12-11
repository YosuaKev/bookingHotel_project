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
    /**
     * Store a newly created booking in database
     */
    public function store(Request $request)
    {
        // Validate incoming data
        $validated = $request->validate([
            'id' => 'required|string',
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'roomType' => 'required|string',
            'checkin' => 'required|string',
            'checkout' => 'required|string|after:checkin',
            'guests' => 'required|integer|min:1|max:10',
            'nights' => 'required|integer|min:1',
            'rate' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'userEmail' => 'email|nullable',
            'userId' => 'integer|nullable'
        ]);

        try {
            // Find user by email if provided
            $user = null;
            if ($validated['userEmail']) {
                $user = User::where('email', $validated['userEmail'])->first();
            }

            // Create booking record
            $booking = Booking::create([
                'user_id' => $user ? $user->id : null,
                'booking_id' => $validated['id'],
                'first_name' => $validated['firstName'],
                'last_name' => $validated['lastName'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'room_type' => $validated['roomType'],
                'check_in' => $validated['checkin'],
                'check_out' => $validated['checkout'],
                'guests' => $validated['guests'],
                'nights' => $validated['nights'],
                'rate' => $validated['rate'],
                'total' => $validated['total'],
                'status' => 'confirmed'
            ]);

            // Create notification instead of sending email
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

    /**
     * Get all bookings for the authenticated user
     */
    public function userBookings()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $bookings = Booking::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'bookings' => $bookings
        ]);
    }

    /**
     * Get a specific booking
     */
    public function show($bookingId)
    {
        $booking = Booking::where('booking_id', $bookingId)->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'booking' => $booking
        ]);
    }
}
