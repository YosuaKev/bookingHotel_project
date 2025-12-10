@include('home.css')
@include('home.header')

<div class="container mt-5">
    <h2>My Bookings</h2>

    @if(session()->has('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    @if($bookings->isEmpty())
        <p>You have no bookings yet.</p>
    @else
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Room</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Status</th>
                    <th>Paid</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookings as $b)
                <tr>
                    <td>{{ $b->id }}</td>
                    <td>{{ optional($b->room)->room_title ?? '—' }}</td>
                    <td>{{ $b->start_date }}</td>
                    <td>{{ $b->end_date }}</td>
                    <td>{{ $b->status ?? 'waiting' }}</td>
                    <td>{{ $b->paid ? 'Yes (ref: '.$b->payment_ref.')' : 'No' }}</td>
                    <td>
                        @if(!$b->paid)
                            <a href="{{ route('booking.pay', $b->id) }}" class="btn btn-sm btn-primary">Pay</a>
                        @else
                            <span class="text-success">Paid</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@include('home.footer')
