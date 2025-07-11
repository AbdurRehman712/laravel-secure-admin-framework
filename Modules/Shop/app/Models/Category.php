<?php

namespace Modules\Shop\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;

    protected $table = 'shop_categories';
    
    protected $fillable = ['name', 'slug', 'description', 'image', 'active', 'sort_order'];

    protected $casts = [
        'active' => 'boolean'
    ];

    protected static function newFactory()
    {
        return \Modules\Shop\database\factories\CategoryFactory::new();
    }

    
    public function products()
    {
        return $this->hasMany(\Modules\Shop\app\Models\Product::class, 'category_id');
    }
}