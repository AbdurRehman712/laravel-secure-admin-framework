<?php

namespace Modules\CmsEditor\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'path',
        'author',
        'version',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the layouts for the theme.
     */
    public function layouts(): HasMany
    {
        return $this->hasMany(Layout::class);
    }
}
