<?php

namespace Modules\CmsEditor\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Layout extends Model
{
    protected $fillable = [
        'theme_id',
        'name',
        'slug',
        'description',
        'content',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the theme that owns the layout.
     */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }
}
