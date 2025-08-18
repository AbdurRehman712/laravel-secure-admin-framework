<?php

namespace App\Services;

use Illuminate\View\View;

class ThemeRenderer
{
    public function render(string $page, array $data = [], string $theme = 'demo'): View
    {
        $bladePagePath = base_path("themes/{$theme}/pages/{$page}.blade.php");
        $bladeLayoutPath = base_path("themes/{$theme}/layouts/default.blade.php");

        if (!file_exists($bladePagePath) || !file_exists($bladeLayoutPath)) {
            abort(404, 'Theme page not found');
        }

        $content = view()->file($bladePagePath, $data)->render();

        return view()->file($bladeLayoutPath, [
            'page' => [
                'title' => $data['title'] ?? 'Page',
                'description' => $data['description'] ?? '',
                'url' => $data['url'] ?? request()->path(),
                'layout' => 'default',
            ],
            'content' => $content,
            'url' => request()->path(),
        ]);
    }
}
