<?php

namespace App\Models;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    // protected $fillable = [
    //     'customer_name',
    //     'mobile',
    //     'address'
    // ];

    protected $fillable = [
        'customer_id',
        'customer_name',
        'mobile',
        'address',

        'staff_id',

        'total_amount',
        'discount',
        'paid_amount',

        'payment_method',
        'payment_status',

        'order_status',

        'notes',

        'image',
        'pdf',
        'audio'
    ];

    protected $casts = [
        'payment_date' => 'date:Y-m-d',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany(\App\Models\OrderPayment::class);
    }
    
    public function latestPayment()
    {
        return $this->hasOne(\App\Models\OrderPayment::class, 'order_id')->latestOfMany();
    }
}
