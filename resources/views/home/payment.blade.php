@include('home.css')
@include('home.header')

<div class="container mt-5">
    <h2>Payment for Booking #{{ $booking->id }}</h2>

    <div class="card">
        <div class="card-body">
            <p><strong>Room:</strong> {{ optional($booking->room)->room_title ?? '—' }}</p>
            <p><strong>Dates:</strong> {{ $booking->start_date }} → {{ $booking->end_date }}</p>
            <p><strong>Amount:</strong> {{ optional($booking->room)->price ?? '—' }}</p>

            <form method="POST" action="{{ route('booking.pay.process', $booking->id) }}">
                @csrf
                <div class="mb-3">
                    <label for="card_number" class="form-label">Card Number</label>
                    <input id="card_number" name="card_number" class="form-control" required />
                </div>
                <button class="btn btn-success" type="submit">Pay Now (demo)</button>
            </form>
        </div>
    </div>
</div>

@include('home.footer')
