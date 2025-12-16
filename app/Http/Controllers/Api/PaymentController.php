<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Create a new payment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|string',
            'payment_method' => 'required|string',
            'cardholder_name' => 'required|string|max:255',
            'card_last_four' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string',
            'billing_address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'user_email' => 'email|nullable'
        ]);

        try {
            // Generate transaction ID
            $transactionId = 'TXN' . date('YmdHis') . random_int(1000, 9999);

            // Create payment record
            $payment = Payment::create([
                'booking_id' => $validated['booking_id'],
                'payment_method' => $validated['payment_method'],
                'cardholder_name' => $validated['cardholder_name'],
                'card_last_four' => $validated['card_last_four'],
                'amount' => $validated['amount'],
                'status' => $validated['status'],
                'billing_address' => $validated['billing_address'],
                'city' => $validated['city'],
                'zip_code' => $validated['zip_code'],
                'country' => $validated['country'],
                'user_email' => $validated['user_email'],
                'transaction_id' => $transactionId
            ]);

            // Try to update booking payment status
            $booking = Booking::where('booking_id', $validated['booking_id'])->first();
            if ($booking) {
                $booking->paid_status = 'paid';
                $booking->save();

                // Create payment confirmation notification
                if ($booking->user_id) {
                    $user = User::find($booking->user_id);
                    if ($user) {
                        NotificationService::notifyPaymentConfirmation($payment, $user, $booking);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'payment' => $payment,
                'transaction_id' => $transactionId
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payments by booking ID
     */
    public function getByBooking($bookingId)
    {
        $payments = Payment::where('booking_id', $bookingId)->get();

        if ($payments->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No payments found for this booking'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'payments' => $payments
        ]);
    }

    /**
     * Get all payments for authenticated user
     */
    public function userPayments()
    {
        $userEmail = request()->input('email');

        if (!$userEmail) {
            return response()->json([
                'success' => false,
                'message' => 'Email required'
            ], 400);
        }

        $payments = Payment::where('user_email', $userEmail)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'payments' => $payments
        ]);
    }

    /**
     * Get payment details
     */
    public function show($paymentId)
    {
        $payment = Payment::where('transaction_id', $paymentId)->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'payment' => $payment
        ]);
    }

    /**
     * Upload payment proof
     */
    public function uploadProof(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|string|exists:bookings,booking_id',
            'proof' => 'required|file|image|max:5120', // 5MB
        ]);

        $user = auth()->user();
        $booking = Booking::where('booking_id', $request->booking_id)->first();

        // Verify ownership
        if ($booking->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            // Store the proof file
            $path = $request->file('proof')->store('payment-proofs', 'public');

            // Create payment record with proof
            $payment = Payment::create([
                'booking_id' => $request->booking_id,
                'payment_method' => 'bank_transfer',
                'status' => 'pending_verification',
                'proof_file' => $path,
                'user_email' => $user->email,
                'amount' => $booking->total,
            ]);

            // Send notification
            NotificationService::notifyPaymentProofReceived($payment, $user, $booking);

            return response()->json([
                'success' => true,
                'message' => 'Payment proof uploaded successfully',
                'payment' => [
                    'id' => $payment->id,
                    'status' => $payment->status,
                    'proof_url' => asset('storage/' . $path),
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin: Verify payment proof
     */
    public function verifyProof(Request $request)
    {
        if (!auth()->user() || auth()->user()->usertype !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required'
            ], 403);
        }

        $request->validate([
            'payment_id' => 'required|integer|exists:payments,id',
            'verified' => 'required|boolean',
            'comment' => 'nullable|string',
        ]);

        $payment = Payment::findOrFail($request->payment_id);
        $booking = Booking::where('booking_id', $payment->booking_id)->first();

        if ($request->verified) {
            $payment->update([
                'status' => 'verified',
                'verified_at' => now(),
                'verified_comment' => $request->comment,
            ]);

            if ($booking) {
                $booking->update(['paid_status' => 'paid']);
            }

            // Notify user of verification
            if ($booking && $booking->user) {
                NotificationService::notifyPaymentVerified($payment, $booking->user, $booking);
            }
        } else {
            $payment->update([
                'status' => 'rejected',
                'verified_comment' => $request->comment,
            ]);

            // Notify user of rejection
            if ($booking && $booking->user) {
                NotificationService::notifyPaymentRejected($payment, $booking->user, $booking);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment ' . ($request->verified ? 'verified' : 'rejected') . ' successfully',
            'payment' => $payment
        ]);
    }
}
