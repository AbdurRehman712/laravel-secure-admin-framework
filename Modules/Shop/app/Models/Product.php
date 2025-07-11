<?php

namespace Modules\Shop\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;

    protected $table = 'shop_products';
    
    protected $fillable = ['name', 'slug', 'description', 'short_description', 'sku', 'price', 'sale_price', 'stock_quantity', 'weight', 'dimensions', 'featured_image', 'gallery', 'status', 'featured', 'meta_title', 'meta_description', 'category_id'];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'featured' => 'boolean'
    ];

    protected static function newFactory()
    {
        return \Modules\Shop\database\factories\ProductFactory::new();
    }

    
    public function category()
    {
        return $this->belongsTo(\Modules\Shop\app\Models\Category::class, 'category_id');
    }
}