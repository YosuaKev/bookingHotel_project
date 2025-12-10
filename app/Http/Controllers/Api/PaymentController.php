<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
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
            'payment_method' => 'required|string|in:credit_card,debit_card,bank_transfer',
            'cardholder_name' => 'required|string|max:255',
            'card_last_four' => 'required|string|size:4',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,completed,failed',
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

            // Update booking payment status
            Booking::where('booking_id', $validated['booking_id'])->update([
                'paid_status' => 'paid'
            ]);

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
}
