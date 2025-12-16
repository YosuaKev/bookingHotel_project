<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Booking;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Get reviews for a room
     */
    public function roomReviews($roomId)
    {
        $reviews = Review::where('room_id', $roomId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'user_name' => $review->user->name,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'verified_booking' => $review->verified_booking,
                    'created_at' => $review->created_at->format('Y-m-d H:i'),
                ];
            }),
            'pagination' => [
                'total' => $reviews->total(),
                'per_page' => $reviews->perPage(),
                'current_page' => $reviews->currentPage(),
            ]
        ]);
    }

    /**
     * Create a review for a booking
     */
    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
            'room_id' => 'required|integer|exists:rooms,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Verify user owns this booking
        $booking = Booking::findOrFail($request->booking_id);
        if ($booking->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Check if user already reviewed this booking
        $existingReview = Review::where('booking_id', $request->booking_id)->exists();
        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this booking'
            ], 422);
        }

        $review = Review::create([
            'user_id' => auth()->id(),
            'room_id' => $request->room_id,
            'booking_id' => $request->booking_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'verified_booking' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review created successfully',
            'data' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
            ]
        ], 201);
    }

    /**
     * Update a review
     */
    public function update($id, Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review = Review::findOrFail($id);

        if ($review->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => $review,
        ]);
    }

    /**
     * Delete a review
     */
    public function destroy($id)
    {
        $review = Review::findOrFail($id);

        if ($review->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully'
        ]);
    }
}
