<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    // Show authenticated user's bookings
    public function index()
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $email = Auth::user()->email;
        $bookings = Booking::where('email', $email)->orderBy('created_at', 'desc')->get();

        return view('home.my_bookings', compact('bookings'));
    }

    // Show payment page for a booking
    public function showPayment($id)
    {
        $booking = Booking::findOrFail($id);

        // Basic permission check: only owner or admin
        if (Auth::check()) {
            if (Auth::user()->usertype !== 'admin' && Auth::user()->email !== $booking->email) {
                return redirect()->back()->with('message', 'Unauthorized');
            }
        }

        return view('home.payment', compact('booking'));
    }

    // Process a dummy payment and mark booking paid
    public function processPayment(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        if (Auth::check()) {
            if (Auth::user()->usertype !== 'admin' && Auth::user()->email !== $booking->email) {
                return redirect()->back()->with('message', 'Unauthorized');
            }
        }

        // Here you would integrate with a real payment gateway. For demo, accept a simple 'card_number'
        $request->validate([
            'card_number' => 'required',
        ]);

        // Mark booking as paid and create a fake payment ref
        $booking->paid = true;
        $booking->payment_ref = 'PAY-' . strtoupper(bin2hex(random_bytes(4)));
        $booking->status = 'paid';
        $booking->save();

        return redirect()->route('my.bookings')->with('message', 'Payment successful. Reference: ' . $booking->payment_ref);
    }
}
