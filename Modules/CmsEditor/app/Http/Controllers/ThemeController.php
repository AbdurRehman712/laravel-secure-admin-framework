<?php

namespace Modules\CmsEditor\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CmsEditor\app\Services\ThemeManager;

class ThemeController extends Controller
{
    protected $themeManager;

    public function __construct(ThemeManager $themeManager)
    {
        $this->themeManager = $themeManager;
    }

    /**
     * Display a list of available themes.
     */
    public function index()
    {
        $themes = $this->themeManager->getAllThemes();

        return response()->json([
            'themes' => $themes,
        ]);
    }

    /**
     * Get theme structure and files.
     */
    public function getThemeStructure(Request $request, $theme)
    {
        // Check if theme exists
        $themeInfo = $this->themeManager->getTheme($theme);
        if (!$themeInfo) {
            return response()->json([
                'error' => 'Theme not found',
            ], 404);
        }
        
        $structure = $this->themeManager->getThemeStructure($theme);
        
        return response()->json([
            'theme' => $themeInfo,
            'structure' => $structure,
        ]);
    }

    /**
     * Get the content of a file in a theme.
     */
    public function getFile(Request $request, $theme, $file = '')
    {
        // Check if theme exists
        $themeInfo = $this->themeManager->getTheme($theme);
        if (!$themeInfo) {
            return response()->json([
                'error' => 'Theme not found',
            ], 404);
        }
        
        $fileInfo = $this->themeManager->getFile($theme, $file);
        if (!$fileInfo) {
            return response()->json([
                'error' => 'File not found',
            ], 404);
        }
        
        return response()->json([
            'theme' => $theme,
            'file' => $fileInfo,
        ]);
    }

    /**
     * Save a file in a theme.
     */
    public function saveFile(Request $request, $theme)
    {
        $request->validate([
            'file' => 'required|string',
            'content' => 'required|string',
            'frontmatter' => 'nullable|string',
        ]);
        
        // Check if theme exists
        $themeInfo = $this->themeManager->getTheme($theme);
        if (!$themeInfo) {
            return response()->json([
                'error' => 'Theme not found',
            ], 404);
        }
        
        $result = $this->themeManager->saveFile(
            $theme,
            $request->file,
            $request->content,
            $request->frontmatter
        );
        
        if (!$result) {
            return response()->json([
                'error' => 'Failed to save file',
            ], 500);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'File saved successfully',
        ]);
    }

    /**
     * Create a new file in a theme.
     */
    public function createFile(Request $request, $theme)
    {
        $request->validate([
            'path' => 'required|string',
            'name' => 'required|string',
            'type' => 'required|in:file,directory',
            'content' => 'required_if:type,file|nullable|string',
        ]);
        
        // Check if theme exists
        $themeInfo = $this->themeManager->getTheme($theme);
        if (!$themeInfo) {
            return response()->json([
                'error' => 'Theme not found',
            ], 404);
        }
        
        $result = $this->themeManager->createFile(
            $theme,
            $request->path,
            $request->name,
            $request->type === 'directory',
            $request->content
        );
        
        if (!$result) {
            return response()->json([
                'error' => 'File or directory already exists or could not be created',
            ], 400);
        }
        
        return response()->json([
            'success' => true,
            'message' => ($request->type === 'directory' ? 'Directory' : 'File') . ' created successfully',
        ]);
    }

    /**
     * Delete a file or directory in a theme.
     */
    public function deleteFile(Request $request, $theme)
    {
        $request->validate([
            'path' => 'required|string',
        ]);
        
        // Check if theme exists
        $themeInfo = $this->themeManager->getTheme($theme);
        if (!$themeInfo) {
            return response()->json([
                'error' => 'Theme not found',
            ], 404);
        }
        
        $result = $this->themeManager->deleteFile($theme, $request->path);
        
        if (!$result) {
            return response()->json([
                'error' => 'File or directory not found or could not be deleted',
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'File or directory deleted successfully',
        ]);
    }
}
