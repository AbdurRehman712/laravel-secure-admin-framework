<?php

namespace Modules\Shop\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;

    protected $table = 'shop_orders';
    
    protected $fillable = ['order_number', 'customer_name', 'customer_email', 'customer_phone', 'billing_address', 'shipping_address', 'subtotal', 'tax_amount', 'shipping_amount', 'total_amount', 'status', 'payment_status', 'payment_method', 'notes'];

    protected $casts = [
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'shipped_at' => 'datetime'
    ];

    protected static function newFactory()
    {
        return \Modules\Shop\database\factories\OrderFactory::new();
    }

    
}