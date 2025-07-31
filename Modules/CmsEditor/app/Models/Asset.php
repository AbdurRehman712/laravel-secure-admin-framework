<?php

namespace Modules\CmsEditor\app\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
        'name',
        'filename',
        'path',
        'mime_type',
        'size',
        'alt',
        'caption',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];
}
