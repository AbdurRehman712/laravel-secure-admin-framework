<?php

namespace Modules\ShopModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    

    protected $table = 'categories';

    protected $fillable = [
        'description',
        'name',
        'slug',
        'status'
    
    ];

    

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
