<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Booking;
use App\Models\User;
use App\Models\Payment;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * Check if user is admin
     */
    private function checkAdmin(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->usertype !== 'admin') {
            return false;
        }
        return true;
    }

    /**
     * Get dashboard statistics
     */
    public function dashboard()
    {
        if (!$this->checkAdmin(request())) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $totalBookings = Booking::count();
        $totalRevenue = Payment::where('status', 'verified')->sum('amount');
        $totalUsers = User::where('usertype', '!=', 'admin')->count();
        $totalRooms = Room::count();

        $pendingPayments = Payment::where('status', 'pending_verification')->count();
        $recentBookings = Booking::orderBy('created_at', 'desc')->limit(5)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_bookings' => $totalBookings,
                'total_revenue' => (float) $totalRevenue,
                'total_users' => $totalUsers,
                'total_rooms' => $totalRooms,
                'pending_payments' => $pendingPayments,
                'recent_bookings' => $recentBookings->map(function ($booking) {
                    return [
                        'id' => $booking->booking_id,
                        'guest_name' => $booking->first_name . ' ' . $booking->last_name,
                        'room_type' => $booking->room_type,
                        'check_in' => $booking->check_in,
                        'total' => $booking->total,
                        'status' => $booking->status,
                    ];
                }),
            ]
        ]);
    }

    /**
     * List all rooms for admin
     */
    public function listRooms()
    {
        if (!$this->checkAdmin(request())) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $rooms = Room::paginate(12);

        return response()->json([
            'success' => true,
            'data' => $rooms->map(function ($room) {
                return [
                    'id' => $room->id,
                    'room_title' => $room->room_title,
                    'room_type' => $room->room_type,
                    'price' => (float) $room->price,
                    'capacity' => $room->capacity,
                    'status' => $room->status,
                    'bookings_count' => $room->bookings()->count(),
                ];
            }),
            'pagination' => [
                'total' => $rooms->total(),
                'per_page' => $rooms->perPage(),
                'current_page' => $rooms->currentPage(),
            ]
        ]);
    }

    /**
     * Create a new room
     */
    public function createRoom(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'room_title' => 'required|string|max:255',
            'room_type' => 'required|string|max:100',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'image' => 'nullable|image|max:2048',
            'wifi' => 'boolean',
            'air_conditioning' => 'boolean',
            'tv' => 'boolean',
            'bathroom_type' => 'string|max:50',
            'amenities' => 'nullable|array',
        ]);

        try {
            $roomData = $validated;

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('rooms', 'public');
                $roomData['image'] = $path;
            }

            $room = Room::create($roomData);

            return response()->json([
                'success' => true,
                'message' => 'Room created successfully',
                'room' => $room
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating room: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update room
     */
    public function updateRoom($id, Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $room = Room::findOrFail($id);

        $validated = $request->validate([
            'room_title' => 'string|max:255',
            'room_type' => 'string|max:100',
            'description' => 'string',
            'price' => 'numeric|min:0',
            'capacity' => 'integer|min:1',
            'image' => 'nullable|image|max:2048',
            'wifi' => 'boolean',
            'air_conditioning' => 'boolean',
            'tv' => 'boolean',
            'bathroom_type' => 'string|max:50',
            'amenities' => 'nullable|array',
            'status' => 'string|in:available,unavailable,maintenance',
        ]);

        try {
            if ($request->hasFile('image')) {
                // Delete old image
                if ($room->image) {
                    Storage::disk('public')->delete($room->image);
                }
                $path = $request->file('image')->store('rooms', 'public');
                $validated['image'] = $path;
            }

            $room->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Room updated successfully',
                'room' => $room
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating room: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete room
     */
    public function deleteRoom($id, Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $room = Room::findOrFail($id);

        try {
            if ($room->image) {
                Storage::disk('public')->delete($room->image);
            }
            $room->delete();

            return response()->json([
                'success' => true,
                'message' => 'Room deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting room: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * List all bookings for admin
     */
    public function listBookings(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $query = Booking::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('paid_status')) {
            $query->where('paid_status', $request->paid_status);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $bookings->map(function ($booking) {
                return [
                    'id' => $booking->booking_id,
                    'guest_name' => $booking->first_name . ' ' . $booking->last_name,
                    'email' => $booking->email,
                    'room_type' => $booking->room_type,
                    'check_in' => $booking->check_in,
                    'check_out' => $booking->check_out,
                    'total' => (float) $booking->total,
                    'status' => $booking->status,
                    'paid_status' => $booking->paid_status,
                ];
            }),
            'pagination' => [
                'total' => $bookings->total(),
                'per_page' => $bookings->perPage(),
                'current_page' => $bookings->currentPage(),
            ]
        ]);
    }

    /**
     * List all users for admin
     */
    public function listUsers(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $users = User::where('usertype', '!=', 'admin')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'total_bookings' => $user->bookings()->count(),
                    'total_spent' => Payment::where('user_email', $user->email)->sum('amount'),
                    'created_at' => $user->created_at->format('Y-m-d'),
                ];
            }),
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
            ]
        ]);
    }

    /**
     * List pending payment verifications
     */
    public function pendingPayments(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $payments = Payment::where('status', 'pending_verification')
            ->with('booking')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'booking_id' => $payment->booking_id,
                    'amount' => (float) $payment->amount,
                    'proof_url' => $payment->proof_file ? asset('storage/' . $payment->proof_file) : null,
                    'submitted_at' => $payment->created_at->format('Y-m-d H:i'),
                ];
            }),
            'pagination' => [
                'total' => $payments->total(),
                'per_page' => $payments->perPage(),
                'current_page' => $payments->currentPage(),
            ]
        ]);
    }

    /**
     * Get reports/analytics
     */
    public function reports(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $period = $request->period ?? 'monthly'; // daily, weekly, monthly, yearly

        // Booking stats by date
        $bookingStats = Booking::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        // Revenue by payment method
        $revenueByMethod = Payment::selectRaw('payment_method, SUM(amount) as total, COUNT(*) as count')
            ->where('status', 'verified')
            ->groupBy('payment_method')
            ->get();

        // Top rooms by bookings
        $topRooms = Booking::selectRaw('room_type, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('room_type')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'booking_stats' => $bookingStats,
                'revenue_by_method' => $revenueByMethod,
                'top_rooms' => $topRooms,
                'total_revenue' => Payment::where('status', 'verified')->sum('amount'),
                'total_bookings' => Booking::count(),
                'average_booking_value' => Booking::avg('total'),
            ]
        ]);
    }

    /**
     * Update room prices (bulk)
     */
    public function updatePrices(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'updates' => 'required|array',
            'updates.*.room_id' => 'required|integer|exists:rooms,id',
            'updates.*.price' => 'required|numeric|min:0',
        ]);

        try {
            foreach ($validated['updates'] as $update) {
                Room::findOrFail($update['room_id'])->update(['price' => $update['price']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Prices updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating prices: ' . $e->getMessage()
            ], 500);
        }
    }
}
