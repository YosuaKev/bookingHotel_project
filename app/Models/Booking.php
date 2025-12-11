<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
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
        'booking_id',
        'status',
        'paid_status',
        'special_requests'
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'total' => 'decimal:2',
        'rate' => 'decimal:2'
    ];

    /**
     * Get the user that owns the booking
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payments for this booking
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'booking_id', 'booking_id');
    }

    /**
     * Get the notifications for this booking
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}