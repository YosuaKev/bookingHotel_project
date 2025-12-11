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
}
