<?php

namespace Modules\CmsEditor\app\Filament\Resources\ThemeResource\Pages;

use Filament\Resources\Pages\Page;
use Modules\CmsEditor\app\Filament\Resources\ThemeResource;
use Modules\CmsEditor\app\Services\ThemeManager;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class EditThemeFile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ThemeResource::class;

    public function getView(): string
    {
        return 'cmseditor::filament.resources.theme-resource.pages.edit-theme-file';
    }

    public $theme;
    public $filePath;
    public $fileContent = '';
    public $fileName = '';
    public $fileExtension = '';
    public $themeStructure = [];
    public $openFiles = [];
    public $activeFile = '';
    public $data = [];
    public $activeTab = 'markup';

    // Form properties for Livewire binding
    public $file_path = '';
    public $content = '';

    // Page metadata properties
    public $pageTitle = '';
    public $pageUrl = '';
    public $pageLayout = '';
    public $pageDescription = '';
    public $pageHidden = false;

    protected $listeners = [
        'pageTitle' => 'updatePageMetadata',
        'pageUrl' => 'updatePageMetadata',
        'pageLayout' => 'updatePageMetadata',
        'pageDescription' => 'updatePageMetadata',
        'pageHidden' => 'updatePageMetadata',
    ];

    public function mount(): void
    {
        // Get theme and file path from route parameters
        $this->theme = request()->route('theme');
        $this->filePath = request()->route('file', '');
        
        // Validate theme exists
        $themeManager = app(ThemeManager::class);
        $themeData = $themeManager->getTheme($this->theme);
        
        if (!$themeData) {
            abort(404, "Theme '{$this->theme}' not found");
        }

        // Load theme structure for file tree
        $this->themeStructure = $themeManager->getThemeStructure($this->theme);
        
        // Load file if specified
        if ($this->filePath) {
            $this->loadFile($this->filePath);
        }
    }

    public function loadFile(string $filePath): void
    {
        $themeManager = app(ThemeManager::class);
        $fileData = $themeManager->getFile($this->theme, $filePath);
        
        if ($fileData) {
            $this->filePath = $filePath;
            $this->fileContent = $fileData['content'] ?? '';
            $this->fileName = basename($filePath);
            $this->fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
            $this->activeFile = $filePath;
            
            // Add to open files if not already open
            if (!in_array($filePath, $this->openFiles)) {
                $this->openFiles[] = $filePath;
            }
            
            // Update form data
            $this->content = $this->fileContent;
            $this->file_path = $this->filePath;

            // Parse page metadata if it's a page file
            if ($this->isPageFile()) {
                $this->parsePageMetadata();
                // Populate form with parsed metadata
                $this->populateFormWithMetadata();
            }
        }
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function isPageFile(): bool
    {
        return str_starts_with($this->filePath, 'pages/') &&
               (in_array($this->fileExtension, ['htm', 'php']) || str_ends_with($this->filePath, '.blade.php'));
    }

    protected function parsePageMetadata(): void
    {
        $content = $this->fileContent;

        // Handle Blade files - extract metadata from corresponding .htm file or set defaults
        if (str_ends_with($this->filePath, '.blade.php')) {
            $this->parseBladePageMetadata();
            return;
        }

        // Parse INI-style metadata from .htm files
        if (preg_match('/^title\s*=\s*"?([^"\r\n]*)"?/m', $content, $matches)) {
            $this->pageTitle = $matches[1];
        }

        if (preg_match('/^url\s*=\s*"?([^"\r\n]*)"?/m', $content, $matches)) {
            $this->pageUrl = $matches[1];
        }

        if (preg_match('/^layout\s*=\s*"?([^"\r\n]*)"?/m', $content, $matches)) {
            $this->pageLayout = $matches[1];
        }

        if (preg_match('/^description\s*=\s*"?([^"\r\n]*)"?/m', $content, $matches)) {
            $this->pageDescription = $matches[1];
        }

        if (preg_match('/^hidden\s*=\s*"?([^"\r\n]*)"?/m', $content, $matches)) {
            $this->pageHidden = filter_var($matches[1], FILTER_VALIDATE_BOOLEAN);
        }
    }

    protected function parseBladePageMetadata(): void
    {
        // For Blade files, get metadata from database or set intelligent defaults
        $pageName = basename($this->filePath, '.blade.php');

        $themePage = \Modules\CmsEditor\app\Models\ThemePage::getPage($this->theme, $pageName);

        if ($themePage) {
            // Load metadata from database
            $this->pageTitle = $themePage->title;
            $this->pageUrl = $themePage->url;
            $this->pageLayout = $themePage->layout;
            $this->pageDescription = $themePage->description;
            $this->pageHidden = $themePage->hidden;
        } else {
            // Set intelligent defaults for Blade files
            $this->setBladePageDefaults($pageName);
        }
    }

    protected function setBladePageDefaults(string $pageName): void
    {
        // Set intelligent defaults based on page name
        $defaults = [
            'home' => [
                'title' => 'Home',
                'url' => '/',
                'description' => 'Welcome to our website',
            ],
            'login' => [
                'title' => 'Login',
                'url' => '/login',
                'description' => 'Sign in to your account',
            ],
            'register' => [
                'title' => 'Register',
                'url' => '/register',
                'description' => 'Create a new account',
            ],
            'dashboard' => [
                'title' => 'Dashboard',
                'url' => '/dashboard',
                'description' => 'User dashboard',
            ],
            'profile' => [
                'title' => 'Profile',
                'url' => '/profile',
                'description' => 'Manage your profile',
            ],
            'settings' => [
                'title' => 'Settings',
                'url' => '/settings',
                'description' => 'Account settings',
            ],
            'forgot-password' => [
                'title' => 'Forgot Password',
                'url' => '/forgot-password',
                'description' => 'Reset your password',
            ],
            'reset-password' => [
                'title' => 'Reset Password',
                'url' => '/reset-password',
                'description' => 'Set a new password',
            ],
        ];

        $pageDefaults = $defaults[$pageName] ?? [
            'title' => ucfirst(str_replace('-', ' ', $pageName)),
            'url' => "/{$pageName}",
            'description' => ucfirst(str_replace('-', ' ', $pageName)) . ' page',
        ];

        $this->pageTitle = $pageDefaults['title'];
        $this->pageUrl = $pageDefaults['url'];
        $this->pageDescription = $pageDefaults['description'];
        $this->pageLayout = 'default';
        $this->pageHidden = false;
    }

    protected function populateFormWithMetadata(): void
    {
        // This will be handled by getFormData() method instead
        // to ensure proper form initialization timing
    }

    protected function getFormData(): array
    {
        return [
            'content' => $this->content,
            'pageTitle' => $this->pageTitle,
            'pageUrl' => $this->pageUrl,
            'pageLayout' => $this->pageLayout,
            'pageDescription' => $this->pageDescription,
            'pageHidden' => $this->pageHidden,
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('file_path')
                ->label('File Path')
                ->disabled()
                ->dehydrated(false)
                ->hiddenLabel(),

            Textarea::make('content')
                ->label('File Content')
                ->hiddenLabel()
                ->rows(30)
                ->extraAttributes([
                    'class' => 'font-mono text-sm bg-gray-900 text-gray-100 border-0 focus:ring-0 resize-none',
                    'style' => 'font-family: "Fira Code", "Monaco", "Consolas", "SF Mono", monospace; line-height: 1.5; padding: 16px; background-color: #1f2937; color: #f9fafb;',
                    'spellcheck' => 'false',
                    'autocomplete' => 'off',
                    'autocorrect' => 'off',
                    'autocapitalize' => 'off'
                ])
                ->reactive()
                ->afterStateUpdated(function ($state) {
                    $this->fileContent = $state;
                }),
        ];
    }

    protected function getMetadataFormSchema(): array
    {
        return [
                TextInput::make('pageTitle')
                    ->label('Title')
                    ->placeholder('Page Title'),

                TextInput::make('pageUrl')
                    ->label('URL')
                    ->placeholder('/page-url'),

                Select::make('pageLayout')
                    ->label('Layout')
                    ->placeholder('Select layout')
                    ->options($this->getAvailableLayouts()),

                Textarea::make('pageDescription')
                    ->label('Description')
                    ->placeholder('Page description')
                    ->rows(3),

                Select::make('pageHidden')
                    ->label('Hidden')
                    ->options([
                        false => 'No',
                        true => 'Yes'
                    ]),
        ];
    }

    public function updatedPageTitle(): void
    {
        $this->updatePageMetadata();
    }

    public function updatedPageUrl(): void
    {
        $this->updatePageMetadata();
    }

    public function updatedPageLayout(): void
    {
        $this->updatePageMetadata();
    }

    public function updatedPageDescription(): void
    {
        $this->updatePageMetadata();
    }

    public function updatedPageHidden(): void
    {
        $this->updatePageMetadata();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save File')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->disabled(fn () => !$this->filePath)
                ->action('saveFile'),

            Action::make('back')
                ->label('Back to Browse')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => static::getResource()::getUrl('browse', ['record' => $this->theme])),
        ];
    }

    public function saveFile(): void
    {
        // Get form data to ensure we have the latest values
        $formData = $this->form->getState();

        // Update properties from form data
        $this->content = $formData['content'] ?? $this->content;
        $this->pageTitle = $formData['pageTitle'] ?? $this->pageTitle;
        $this->pageUrl = $formData['pageUrl'] ?? $this->pageUrl;
        $this->pageLayout = $formData['pageLayout'] ?? $this->pageLayout;
        $this->pageDescription = $formData['pageDescription'] ?? $this->pageDescription;
        $this->pageHidden = $formData['pageHidden'] ?? $this->pageHidden;

        if (!$this->filePath) {
            Notification::make()
                ->title('Error')
                ->body('No file selected to save.')
                ->danger()
                ->send();
            return;
        }

        $themeManager = app(ThemeManager::class);

        try {
            $success = $themeManager->saveFile($this->theme, $this->filePath, $this->content);

            // Save metadata for Blade files
            if ($success && $this->isPageFile() && str_ends_with($this->filePath, '.blade.php')) {
                $this->saveBladePageMetadata();
            }

            if ($success) {
                $message = "File '{$this->fileName}' saved successfully.";
                if ($this->isPageFile() && str_ends_with($this->filePath, '.blade.php')) {
                    $message .= " Page settings saved.";

                    // Show URL change warning if applicable
                    if ($this->pageUrl) {
                        $this->updateRouteForPage();
                    }
                }

                Notification::make()
                    ->title('Success')
                    ->body($message)
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Error')
                    ->body("Failed to save file '{$this->fileName}'.")
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body("Error saving file: " . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function closeFile(string $filePath): void
    {
        $this->openFiles = array_filter($this->openFiles, fn($file) => $file !== $filePath);
        
        if ($this->activeFile === $filePath) {
            $this->activeFile = '';
            $this->filePath = '';
            $this->fileContent = '';
            $this->fileName = '';
            $this->content = '';
            $this->file_path = '';
        }
    }

    protected function saveBladePageMetadata(): void
    {
        // Save metadata to database
        $pageName = basename($this->filePath, '.blade.php');

        $metadata = [
            'title' => $this->pageTitle ?: ucfirst(str_replace('-', ' ', $pageName)),
            'url' => $this->pageUrl ?: ($pageName === 'home' ? '/' : "/{$pageName}"),
            'layout' => $this->pageLayout ?: 'default',
            'description' => $this->pageDescription ?: '',
            'hidden' => $this->pageHidden ?: false,
        ];

        \Modules\CmsEditor\app\Models\ThemePage::updatePageMetadata($this->theme, $pageName, $metadata);
    }

    public function updatePageMetadata(): void
    {
        // This method is called when metadata fields are updated via Livewire
        // We don't auto-save here - saving only happens when "Save File" button is clicked
        // This allows users to make changes without immediately persisting them
    }

    protected function updateRouteForPage(): void
    {
        // For now, just notify the user that URL changes require manual route updates
        // In a production system, you might want to automatically update routes

        $pageName = basename($this->filePath, '.blade.php');

        // Check if this is a core page that has named routes
        $corePages = ['login', 'register', 'dashboard', 'profile', 'settings'];

        if (in_array($pageName, $corePages)) {
            Notification::make()
                ->title('URL Updated')
                ->body("Page URL updated to '{$this->pageUrl}'. Note: Named routes for core pages like '{$pageName}' may need manual updates in routes/web.php")
                ->warning()
                ->send();
        }
    }

    public function getTitle(): string
    {
        return "Theme Editor: {$this->theme}" . ($this->fileName ? " - {$this->fileName}" : '');
    }
}
