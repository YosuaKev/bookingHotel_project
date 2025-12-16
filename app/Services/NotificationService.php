<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Booking;

class NotificationService
{
    /**
     * Create a booking confirmation notification
     */
    public static function notifyBookingConfirmation(Booking $booking, ?User $user = null)
    {
        try {
            if (!$user && $booking->user_id) {
                $user = User::find($booking->user_id);
            }

            if (!$user) {
                return false;
            }

            Notification::create([
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'type' => 'booking_confirmation',
                'title' => 'Booking Confirmed',
                'message' => "Your booking for {$booking->room_type} from {$booking->check_in} to {$booking->check_out} has been confirmed. Booking ID: {$booking->booking_id}",
                'status' => 'unread'
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Error creating booking notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a payment confirmation notification
     */
    public static function notifyPaymentConfirmation($payment, User $user, Booking $booking)
    {
        try {
            Notification::create([
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'type' => 'payment_received',
                'title' => 'Payment Received',
                'message' => "Payment of \$" . number_format($payment->amount, 2) . " has been received for your booking. Transaction ID: {$payment->transaction_id}",
                'status' => 'unread'
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Error creating payment notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a generic notification
     */
    public static function notify(User $user, Booking $booking = null, $type = 'info', $title = '', $message = '')
    {
        try {
            Notification::create([
                'user_id' => $user->id,
                'booking_id' => $booking ? $booking->id : null,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'status' => 'unread'
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Error creating notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Booking cancellation notification
     */
    public static function notifyBookingCancellation(Booking $booking, User $user, $refundAmount = null)
    {
        try {
            $message = "Your booking for {$booking->room_type} has been cancelled.";
            if ($refundAmount) {
                $message .= " A refund of \$" . number_format($refundAmount, 2) . " has been processed.";
            }

            Notification::create([
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'type' => 'booking_cancelled',
                'title' => 'Booking Cancelled',
                'message' => $message,
                'status' => 'unread'
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Error creating cancellation notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Payment proof received notification
     */
    public static function notifyPaymentProofReceived($payment, User $user, Booking $booking)
    {
        try {
            Notification::create([
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'type' => 'payment_proof_received',
                'title' => 'Payment Proof Received',
                'message' => "We've received your payment proof for \$" . number_format($payment->amount, 2) . ". It's being verified and you'll be notified once approved.",
                'status' => 'unread'
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Error creating proof received notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Payment verified notification
     */
    public static function notifyPaymentVerified($payment, User $user, Booking $booking)
    {
        try {
            Notification::create([
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'type' => 'payment_verified',
                'title' => 'Payment Verified',
                'message' => "Your payment of \$" . number_format($payment->amount, 2) . " has been verified and approved. Your booking is confirmed!",
                'status' => 'unread'
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Error creating payment verified notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Payment rejected notification
     */
    public static function notifyPaymentRejected($payment, User $user, Booking $booking)
    {
        try {
            $message = "Your payment proof was not approved.";
            if ($payment->verified_comment) {
                $message .= " Reason: {$payment->verified_comment}";
            }
            $message .= " Please resubmit or contact support.";

            Notification::create([
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'type' => 'payment_rejected',
                'title' => 'Payment Rejected',
                'message' => $message,
                'status' => 'unread'
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Error creating payment rejected notification: ' . $e->getMessage());
            return false;
        }
    }
}
