<?php

namespace Modules\CmsEditor\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ThemePage extends Model
{
    use HasFactory;

    protected $fillable = [
        'theme_name',
        'page_name',
        'file_path',
        'title',
        'url',
        'layout',
        'description',
        'hidden',
        'meta_data',
    ];

    protected $casts = [
        'hidden' => 'boolean',
        'meta_data' => 'array',
    ];

    /**
     * Get page by theme and page name
     */
    public static function getPage(string $themeName, string $pageName): ?self
    {
        return static::where('theme_name', $themeName)
            ->where('page_name', $pageName)
            ->first();
    }

    /**
     * Create or update page metadata
     */
    public static function updatePageMetadata(string $themeName, string $pageName, array $data): self
    {
        return static::updateOrCreate(
            [
                'theme_name' => $themeName,
                'page_name' => $pageName,
            ],
            array_merge($data, [
                'file_path' => "pages/{$pageName}.blade.php",
            ])
        );
    }

    /**
     * Get all pages for a theme
     */
    public static function getThemePages(string $themeName): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('theme_name', $themeName)
            ->orderBy('page_name')
            ->get();
    }

    /**
     * Get page by URL
     */
    public static function getPageByUrl(string $themeName, string $url): ?self
    {
        return static::where('theme_name', $themeName)
            ->where('url', $url)
            ->where('hidden', false)
            ->first();
    }
}
