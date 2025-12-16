<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'payment_method',
        'cardholder_name',
        'card_last_four',
        'amount',
        'status',
        'billing_address',
        'city',
        'zip_code',
        'country',
        'user_email',
        'transaction_id',
        'proof_file',
        'verified_at',
        'verified_comment',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the booking associated with this payment
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }
}
