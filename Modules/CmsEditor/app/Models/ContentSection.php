<?php

namespace Modules\CmsEditor\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentSection extends Model
{
    protected $fillable = [
        'page_id',
        'name',
        'type',
        'content',
        'order',
        'settings',
    ];

    protected $casts = [
        'settings' => 'json',
    ];

    /**
     * Get the page that owns the content section.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
