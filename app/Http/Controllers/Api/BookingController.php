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

    // Cancel booking dengan refund
    public function cancel(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|string|exists:bookings,booking_id'
        ]);

        $user = Auth::user();
        $booking = Booking::where('booking_id', $request->booking_id)->first();

        // Verify ownership
        if ($booking->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Check if booking can be cancelled
        if (in_array($booking->status, ['cancelled', 'completed'])) {
            return response()->json([
                'success' => false,
                'message' => 'This booking cannot be cancelled'
            ], 422);
        }

        // Calculate refund (100% for now, can be modified for cancellation policies)
        $refundAmount = $booking->total;
        $daysUntilCheckIn = now()->diffInDays(new \DateTime($booking->check_in));

        // Apply cancellation policy: 50% refund if within 7 days
        if ($daysUntilCheckIn <= 7) {
            $refundAmount = $booking->total * 0.5;
        }

        // Update booking status
        $booking->update([
            'status' => 'cancelled',
            'refund_amount' => $refundAmount,
            'cancelled_at' => now(),
        ]);

        // Create refund notification
        if ($user) {
            NotificationService::notifyBookingCancellation($booking, $user, $refundAmount);
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully',
            'refund_amount' => $refundAmount,
            'booking' => $booking
        ]);
    }

    // Get available dates for a room
    public function getRoomAvailability(Request $request)
    {
        $request->validate([
            'room_type' => 'required|string',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
        ]);

        // Check if room is available for the given dates
        $booking = Booking::where('room_type', $request->room_type)
            ->where('status', '!=', 'cancelled')
            ->where('paid_status', 'paid')
            ->where(function ($query) use ($request) {
                $query->whereBetween('check_in', [$request->check_in, $request->check_out])
                    ->orWhereBetween('check_out', [$request->check_in, $request->check_out])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('check_in', '<=', $request->check_in)
                          ->where('check_out', '>=', $request->check_out);
                    });
            })
            ->exists();

        return response()->json([
            'success' => true,
            'available' => !$booking,
            'room_type' => $request->room_type,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
        ]);
    }
}
