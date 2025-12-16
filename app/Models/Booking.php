<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'booking_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'room_type',
        'check_in',
        'check_out',
        'guests',
        'nights',
        'rate',
        'total',
        'status',
        'special_requests',
        'paid_status',
        'refund_amount',
        'cancelled_at',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'total' => 'decimal:2',
        'rate' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'booking_id', 'booking_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
}
