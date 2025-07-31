<?php

namespace Modules\CmsEditor\app\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

class ThemeManager
{
    /**
     * Get the themes directory path
     */
    public function getThemesPath(): string
    {
        return base_path('themes');
    }

    /**
     * Get all available themes
     */
    public function getAllThemes(): array
    {
        $themes = [];
        $themesPath = $this->getThemesPath();

        if (File::isDirectory($themesPath)) {
            $directories = File::directories($themesPath);

            foreach ($directories as $directory) {
                $themeYamlPath = $directory . '/theme.yaml';
                
                if (File::exists($themeYamlPath)) {
                    $themeName = basename($directory);
                    $themeConfig = Yaml::parseFile($themeYamlPath);
                    
                    $themes[] = [
                        'code' => $themeName,
                        'name' => $themeConfig['name'] ?? $themeName,
                        'description' => $themeConfig['description'] ?? '',
                        'author' => $themeConfig['author'] ?? 'Unknown',
                        'path' => $directory,
                    ];
                }
            }
        }

        return $themes;
    }

    /**
     * Get a specific theme by its code
     */
    public function getTheme(string $themeCode): ?array
    {
        $themes = $this->getAllThemes();
        
        foreach ($themes as $theme) {
            if ($theme['code'] === $themeCode) {
                return $theme;
            }
        }
        
        return null;
    }

    /**
     * Get the structure of a theme
     */
    public function getThemeStructure(string $themeCode): array
    {
        $themePath = $this->getThemesPath() . '/' . $themeCode;
        
        if (!File::isDirectory($themePath)) {
            return [];
        }
        
        return $this->scanDirectory($themePath);
    }

    /**
     * Recursively scan a directory
     */
    protected function scanDirectory(string $path, string $relativePath = ''): array
    {
        $result = [];
        
        // Get files
        $files = File::files($path);
        foreach ($files as $file) {
            $fileName = $file->getFilename();
            
            // Skip hidden files
            if (strpos($fileName, '.') === 0 && $fileName !== '.gitignore') {
                continue;
            }
            
            $extension = $file->getExtension();
            
            $result[] = [
                'type' => 'file',
                'name' => $fileName,
                'path' => $relativePath ? $relativePath . '/' . $fileName : $fileName,
                'extension' => $extension,
                'size' => File::size($file),
                'last_modified' => File::lastModified($file),
            ];
        }
        
        // Get directories
        $directories = File::directories($path);
        foreach ($directories as $directory) {
            $dirName = basename($directory);
            
            // Skip hidden directories
            if (strpos($dirName, '.') === 0) {
                continue;
            }
            
            $dirRelativePath = $relativePath ? $relativePath . '/' . $dirName : $dirName;
            
            $result[] = [
                'type' => 'directory',
                'name' => $dirName,
                'path' => $dirRelativePath,
                'children' => $this->scanDirectory($directory, $dirRelativePath),
            ];
        }
        
        return $result;
    }

    /**
     * Get a file from a theme
     */
    public function getFile(string $themeCode, string $filePath): ?array
    {
        $fullPath = $this->getThemesPath() . '/' . $themeCode . '/' . $filePath;
        
        if (!File::exists($fullPath) || File::isDirectory($fullPath)) {
            return null;
        }
        
        $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
        $content = File::get($fullPath);
        
        // Parse frontmatter for .htm files
        $frontmatter = null;
        $body = $content;
        
        if ($extension === 'htm') {
            $parts = explode('==', $content, 2);
            if (count($parts) === 2) {
                $frontmatter = trim($parts[0]);
                $body = trim($parts[1]);
            }
        }
        
        return [
            'path' => $filePath,
            'name' => basename($filePath),
            'extension' => $extension,
            'content' => $body,
            'frontmatter' => $frontmatter,
            'last_modified' => File::lastModified($fullPath),
            'size' => File::size($fullPath),
        ];
    }

    /**
     * Save a file to a theme
     */
    public function saveFile(string $themeCode, string $filePath, string $content, ?string $frontmatter = null): bool
    {
        $fullPath = $this->getThemesPath() . '/' . $themeCode . '/' . $filePath;
        $directory = dirname($fullPath);
        
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
        
        $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
        
        // Combine frontmatter and content for .htm files
        if ($extension === 'htm' && $frontmatter !== null) {
            $content = $frontmatter . "\n==\n" . $content;
        }
        
        return (bool) File::put($fullPath, $content);
    }

    /**
     * Create a new file or directory in a theme
     */
    public function createFile(string $themeCode, string $path, string $name, bool $isDirectory, ?string $content = null): bool
    {
        $fullPath = $this->getThemesPath() . '/' . $themeCode . '/' . $path . '/' . $name;
        
        if (File::exists($fullPath)) {
            return false;
        }
        
        if ($isDirectory) {
            return File::makeDirectory($fullPath, 0755, true);
        } else {
            return (bool) File::put($fullPath, $content ?? '');
        }
    }

    /**
     * Delete a file or directory from a theme
     */
    public function deleteFile(string $themeCode, string $path): bool
    {
        $fullPath = $this->getThemesPath() . '/' . $themeCode . '/' . $path;
        
        if (!File::exists($fullPath)) {
            return false;
        }
        
        if (File::isDirectory($fullPath)) {
            return File::deleteDirectory($fullPath);
        } else {
            return File::delete($fullPath);
        }
    }
}
