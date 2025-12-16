<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Get all rooms with optional filtering
     */
    public function index(Request $request)
    {
        $query = Room::where('status', 'available');

        // Filter by room type
        if ($request->has('room_type')) {
            $query->where('room_type', $request->room_type);
        }

        // Filter by price range
        if ($request->has('min_price') && $request->has('max_price')) {
            $query->whereBetween('price', [
                floatval($request->min_price),
                floatval($request->max_price)
            ]);
        }

        // Filter by capacity
        if ($request->has('capacity')) {
            $query->where('capacity', '>=', intval($request->capacity));
        }

        // Filter by amenities (all must match)
        if ($request->has('amenities')) {
            $amenities = $request->amenities;
            if (is_string($amenities)) {
                $amenities = json_decode($amenities, true);
            }
            foreach ($amenities as $amenity) {
                $query->whereJsonContains('amenities', $amenity);
            }
        }

        $rooms = $query->with('reviews')->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $rooms->map(function ($room) {
                return [
                    'id' => $room->id,
                    'room_title' => $room->room_title,
                    'room_type' => $room->room_type,
                    'description' => $room->description,
                    'price' => (float) $room->price,
                    'capacity' => $room->capacity,
                    'image' => $room->image,
                    'amenities' => $room->amenities ?? [],
                    'rating' => round($room->averageRating(), 1),
                    'review_count' => $room->reviews()->count(),
                ];
            }),
            'pagination' => [
                'total' => $rooms->total(),
                'per_page' => $rooms->perPage(),
                'current_page' => $rooms->currentPage(),
                'last_page' => $rooms->lastPage(),
            ]
        ]);
    }

    /**
     * Get single room details
     */
    public function show($id)
    {
        $room = Room::with('reviews.user')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $room->id,
                'room_title' => $room->room_title,
                'room_type' => $room->room_type,
                'description' => $room->description,
                'price' => (float) $room->price,
                'capacity' => $room->capacity,
                'image' => $room->image,
                'wifi' => $room->wifi,
                'air_conditioning' => $room->air_conditioning,
                'tv' => $room->tv,
                'bathroom_type' => $room->bathroom_type,
                'amenities' => $room->amenities ?? [],
                'rating' => round($room->averageRating(), 1),
                'reviews' => $room->reviews->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'user_name' => $review->user->name,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'verified_booking' => $review->verified_booking,
                        'created_at' => $review->created_at->format('Y-m-d'),
                    ];
                }),
            ]
        ]);
    }

    /**
     * Check room availability for date range
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'room_id' => 'required|integer|exists:rooms,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
        ]);

        $room = Room::findOrFail($request->room_id);
        $isAvailable = $room->isAvailable($request->check_in, $request->check_out);

        return response()->json([
            'success' => true,
            'available' => $isAvailable,
            'room_id' => $room->id,
            'room_title' => $room->room_title,
            'price' => (float) $room->price,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
        ]);
    }

    /**
     * Get availability calendar for a room
     */
    public function getAvailabilityCalendar($id, Request $request)
    {
        $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2024',
        ]);

        $room = Room::findOrFail($id);
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $bookedDates = $room->bookings()
            ->where('status', '!=', 'cancelled')
            ->where('paid_status', 'paid')
            ->get()
            ->map(function ($booking) {
                $dates = [];
                $current = strtotime($booking->check_in);
                $end = strtotime($booking->check_out);
                while ($current < $end) {
                    $dates[] = date('Y-m-d', $current);
                    $current = strtotime('+1 day', $current);
                }
                return $dates;
            })
            ->flatten()
            ->unique()
            ->values();

        return response()->json([
            'success' => true,
            'room_id' => $room->id,
            'month' => $month,
            'year' => $year,
            'booked_dates' => $bookedDates,
        ]);
    }
}
