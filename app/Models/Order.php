<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'total_amount',
        'shipping_address',
        'billing_address',
        'status',
        // FIX: Added these two fields so they can be saved to the database
        'payment_method',
        'phone',
    ];

    /**
     * An Order belongs to a User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * An Order has many OrderDetails (Items).
     */
    public function items()
    {
        return $this->hasMany(OrderDetail::class);
    }
}
