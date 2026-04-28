<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    protected $fillable = [
        'order_id',
        'amount',
        'payment_date',
        'payment_mode',
        'notes'
    ];

    // public function order()
    // {
    //     return $this->belongsTo(Order::class);
    // }

    // Model
    protected $casts = [
        'payment_date' => 'datetime',
    ];

    public function getPaymentDateAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('Y-m-d');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}