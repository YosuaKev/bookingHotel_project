<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'room_title',
        'room_type',
        'description',
        'price',
        'capacity',
        'image',
        'wifi',
        'air_conditioning',
        'tv',
        'bathroom_type',
        'amenities',
        'status',
    ];

    protected $casts = [
        'amenities' => 'array',
        'price' => 'decimal:2',
        'capacity' => 'integer',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function isAvailable($checkIn, $checkOut)
    {
        $bookings = $this->bookings()
            ->where('status', '!=', 'cancelled')
            ->where('paid_status', 'paid')
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out', [$checkIn, $checkOut])
                    ->orWhere(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in', '<=', $checkIn)
                          ->where('check_out', '>=', $checkOut);
                    });
            })
            ->exists();

        return !$bookings;
    }

    public function averageRating()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }
}
