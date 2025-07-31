<?php

namespace Modules\CmsEditor\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\CmsEditor\app\Models\Page;
use Modules\CmsEditor\app\Models\Theme;

class CmsPageController extends Controller
{
    /**
     * Display a page by its slug.
     */
    public function showPage(string $slug = 'home'): View
    {
        $page = Page::where('slug', $slug)
            ->where('is_published', true)
            ->with(['contentSections', 'parent'])
            ->firstOrFail();

        // Get active theme
        $theme = Theme::where('is_active', true)->first();
        
        $layout = $page->layout;
        
        // If theme has a specific layout, use it
        if ($theme) {
            $themeLayout = $theme->layouts()->where('slug', $layout)->first();
            if ($themeLayout) {
                return view('cmseditor::themes.' . $theme->slug . '.layouts.' . $layout, [
                    'page' => $page,
                    'content' => $page->contentSections->groupBy('name'),
                ]);
            }
        }
        
        // Fallback to default layout
        return view('cmseditor::layouts.' . $layout, [
            'page' => $page,
            'content' => $page->contentSections->groupBy('name'),
        ]);
    }

    /**
     * Preview a page (admin only).
     */
    public function previewPage(Page $page): View
    {
        // Check if user has permission
        if (!auth()->user()->can('view_page')) {
            abort(403);
        }
        
        // Get active theme
        $theme = Theme::where('is_active', true)->first();
        
        $layout = $page->layout;
        
        // If theme has a specific layout, use it
        if ($theme) {
            $themeLayout = $theme->layouts()->where('slug', $layout)->first();
            if ($themeLayout) {
                return view('cmseditor::themes.' . $theme->slug . '.layouts.' . $layout, [
                    'page' => $page,
                    'content' => $page->contentSections->groupBy('name'),
                    'preview' => true,
                ]);
            }
        }
        
        // Fallback to default layout
        return view('cmseditor::layouts.' . $layout, [
            'page' => $page,
            'content' => $page->contentSections->groupBy('name'),
            'preview' => true,
        ]);
    }
}
