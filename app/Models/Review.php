<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'room_id',
        'booking_id',
        'rating',
        'comment',
        'verified_booking',
    ];

    protected $casts = [
        'rating' => 'integer',
        'verified_booking' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
