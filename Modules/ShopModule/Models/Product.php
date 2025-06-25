<?php

namespace Modules\ShopModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    

    protected $table = 'products';

    protected $fillable = [
        'category_id',
        'description',
        'name',
        'price',
        'sku',
        'status',
        'stock_quantity'
    
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
