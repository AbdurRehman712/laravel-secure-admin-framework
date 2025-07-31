<?php

namespace Modules\CmsEditor\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Models\User;
use Modules\CmsEditor\app\Models\ThemePage;
use Modules\CmsEditor\app\Services\ThemeManager;

class ThemePageController extends Controller
{
    protected ThemeManager $themeManager;

    public function __construct(ThemeManager $themeManager)
    {
        $this->themeManager = $themeManager;
    }

    /**
     * Display a theme page by its URL.
     */
    public function showPage(Request $request, string $url = '/'): Response|View
    {
        // Get the active theme (for now, we'll use 'demo' as default)
        $activeTheme = 'demo';

        // Clean up the URL
        $url = '/' . ltrim($url, '/');
        if ($url === '/') {
            $url = '/';
        }

        // Map URLs to page files
        $pageMapping = [
            '/' => 'home',
            '/login' => 'login',
            '/register' => 'register',
            '/dashboard' => 'dashboard',
            '/profile' => 'profile',
            '/settings' => 'settings',
            '/forgot-password' => 'forgot-password',
            '/reset-password' => 'reset-password',
        ];

        $pageName = $pageMapping[$url] ?? null;

        if (!$pageName) {
            abort(404, "Page not found for URL: {$url}");
        }

        // Check if Blade file exists
        $bladePagePath = base_path("themes/{$activeTheme}/pages/{$pageName}.blade.php");
        $bladeLayoutPath = base_path("themes/{$activeTheme}/layouts/default.blade.php");

        if (file_exists($bladePagePath) && file_exists($bladeLayoutPath)) {
            // Use Blade rendering
            return $this->renderBladePage($activeTheme, $pageName, $url);
        }

        // Fallback to .htm files
        return $this->renderHtmPage($activeTheme, $url);
    }

    /**
     * Render a Blade-based theme page.
     */
    protected function renderBladePage(string $theme, string $pageName, string $url): View
    {
        // Get page metadata from .htm file if it exists
        $pageMetadata = $this->getPageMetadata($theme, $pageName);

        // Check if the Blade file exists
        $bladeFilePath = base_path("themes/{$theme}/pages/{$pageName}.blade.php");

        if (!file_exists($bladeFilePath)) {
            abort(404, "Blade file not found: {$bladeFilePath}");
        }

        // Get page content by rendering the Blade file
        try {
            $pageContent = view()->file($bladeFilePath)->render();
        } catch (\Exception $e) {
            // If there's an error rendering the Blade file, show the error
            $pageContent = "<div class='alert alert-danger'>Error rendering page: " . $e->getMessage() . "</div>";
        }

        // Render with layout
        return view()->file(
            base_path("themes/{$theme}/layouts/default.blade.php"),
            [
                'page' => $pageMetadata,
                'content' => $pageContent,
                'url' => $url,
            ]
        );
    }

    /**
     * Get page metadata from .htm file or defaults.
     */
    protected function getPageMetadata(string $theme, string $pageName): array
    {
        $htmFile = "pages/{$pageName}.htm";
        $pageData = $this->themeManager->getFile($theme, $htmFile);

        if ($pageData) {
            $parsed = $this->parsePage($pageData['content']);
            return [
                'title' => $parsed['title'],
                'description' => $parsed['description'],
                'url' => $parsed['url'],
                'layout' => $parsed['layout'],
            ];
        }

        // Default metadata
        return [
            'title' => ucfirst($pageName),
            'description' => ucfirst($pageName) . ' page',
            'url' => $pageName === 'home' ? '/' : "/{$pageName}",
            'layout' => 'default',
        ];
    }

    /**
     * Fallback to render .htm files.
     */
    protected function renderHtmPage(string $theme, string $url): Response
    {
        // Find the page file that matches this URL
        $pageFile = $this->findPageByUrl($theme, $url);

        if (!$pageFile) {
            abort(404, "Page not found for URL: {$url}");
        }

        // Get the page content
        $pageData = $this->themeManager->getFile($theme, $pageFile);

        if (!$pageData) {
            abort(404, "Page file not found: {$pageFile}");
        }

        // Parse the page metadata and content
        $parsedPage = $this->parsePage($pageData['content']);

        // Get the layout
        $layout = $parsedPage['layout'] ?? 'default';
        $layoutFile = "layouts/{$layout}.htm";

        // Get layout content
        $layoutData = $this->themeManager->getFile($theme, $layoutFile);

        if (!$layoutData) {
            // Fallback to a simple layout
            return response($parsedPage['content'], 200)
                ->header('Content-Type', 'text/html');
        }

        // Parse layout
        $parsedLayout = $this->parsePage($layoutData['content']);

        // Replace {% page %} placeholder in layout with page content
        $finalContent = str_replace('{% page %}', $parsedPage['content'], $parsedLayout['content']);

        return response($finalContent, 200)
            ->header('Content-Type', 'text/html');
    }

    /**
     * Find a page file by its URL.
     */
    protected function findPageByUrl(string $theme, string $url): ?string
    {
        try {
            $themeStructure = $this->themeManager->getThemeStructure($theme);

            if (!isset($themeStructure['pages'])) {
                return null;
            }

            // Check each page file
            foreach ($this->flattenPages($themeStructure['pages']) as $pageFile) {
                $pageData = $this->themeManager->getFile($theme, $pageFile);

                if ($pageData) {
                    $parsedPage = $this->parsePage($pageData['content']);
                    $pageUrl = $parsedPage['url'] ?? '';

                    // Match the URL
                    if ($pageUrl === $url || ($url === '/' && $pageUrl === '/')) {
                        return $pageFile;
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Flatten the pages structure to get all page files.
     */
    protected function flattenPages(array $pages): array
    {
        $files = [];

        foreach ($pages as $page) {
            if (isset($page['type']) && $page['type'] === 'file') {
                $files[] = $page['path'];
            } elseif (isset($page['children'])) {
                $files = array_merge($files, $this->flattenPages($page['children']));
            }
        }

        return $files;
    }

    /**
     * Parse a page file to extract metadata and content.
     */
    protected function parsePage(string $content): array
    {
        $metadata = [];
        $pageContent = $content;

        // Check if the file has metadata section (INI-style at the top)
        if (preg_match('/^(.*?)==\s*$/ms', $content, $matches)) {
            $metadataSection = $matches[1];
            $pageContent = substr($content, strlen($matches[0]));

            // Parse metadata
            $lines = explode("\n", $metadataSection);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || strpos($line, '=') === false) {
                    continue;
                }

                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, ' "\'');

                $metadata[$key] = $value;
            }
        }

        return [
            'title' => $metadata['title'] ?? 'Untitled',
            'url' => $metadata['url'] ?? '/',
            'layout' => $metadata['layout'] ?? 'default',
            'description' => $metadata['description'] ?? '',
            'hidden' => filter_var($metadata['hidden'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'content' => trim($pageContent),
            'metadata' => $metadata,
        ];
    }

    /**
     * Handle login form submission.
     */
    public function handleLogin(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard')
                ->with('success', 'Welcome back!');
        }

        return redirect()->back()
            ->withErrors(['email' => 'Invalid credentials'])
            ->withInput();
    }

    /**
     * Handle registration form submission.
     */
    public function handleRegister(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign default user role
        $user->assignRole('user');

        Auth::login($user);

        return redirect('/dashboard')
            ->with('success', 'Account created successfully!');
    }

    /**
     * Handle logout.
     */
    public function handleLogout(Request $request): RedirectResponse
    {
        // Only logout from the web guard, don't invalidate the entire session
        Auth::guard('web')->logout();

        // Regenerate token for security but keep session for other guards
        $request->session()->regenerateToken();

        return redirect('/login')
            ->with('success', 'You have been logged out.');
    }

    /**
     * Handle forgot password form submission.
     */
    public function handleForgotPassword(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? redirect()->back()->with('success', 'Password reset link sent!')
            : redirect()->back()->withErrors(['email' => __($status)]);
    }

    /**
     * Handle password reset form submission.
     */
    public function handleResetPassword(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect('/login')->with('success', 'Password reset successfully!')
            : redirect()->back()->withErrors(['email' => [__($status)]]);
    }

    /**
     * Handle profile update.
     */
    public function handleProfileUpdate(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check current password if new password is provided
        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->back()
                    ->withErrors(['current_password' => 'Current password is incorrect'])
                    ->withInput();
            }
            $user->password = Hash::make($request->password);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        return redirect()->back()
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Handle settings update.
     */
    public function handleSettingsUpdate(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'timezone' => 'required|string',
            'language' => 'required|string',
            'theme' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update user settings (you might want to create a settings table)
        // For now, we'll just redirect back with success

        return redirect()->back()
            ->with('success', 'Settings updated successfully!');
    }
}
