<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'table_id',
        'status',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax',
        'total_price',
        'note',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'discount_value' => 'integer',
        'discount_amount' => 'integer',
        'tax' => 'integer',
        'total_price' => 'decimal:2',
    ];

    public function order_items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function table()
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }
}
