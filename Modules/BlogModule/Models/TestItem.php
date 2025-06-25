<?php

namespace Modules\BlogModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestItem extends Model
{
    

    protected $table = 'test_items';

    protected $fillable = [
        'description',
        'is_active',
        'price',
        'title'
    
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2'
    ];

    
}
