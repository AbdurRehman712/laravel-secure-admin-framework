<?php

namespace Modules\ModuleBuilder\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Modules\ModuleBuilder\Models\ModuleProject;
use Modules\ModuleBuilder\Services\EnhancedModuleGeneratorService;
use Filament\Notifications\Notification;

class ModuleBuilderDashboard extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected string $view = 'module-builder::pages.module-builder-dashboard';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Module Builder Dashboard';
    protected static \UnitEnum|string|null $navigationGroup = 'Module Builder';
    protected static ?int $navigationSort = 0;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_any_module_project') ?? false;
    }

    public function mount(): void
    {
        // Initialize page data
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_module')
                ->label('New Module Project')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url('/admin/module-projects/create'),
                
            Action::make('quick_start_guide')
                ->label('Quick Start Guide')
                ->icon('heroicon-o-question-mark-circle')
                ->color('gray')
                ->modalHeading('Module Builder Quick Start')
                ->modalContent(view('module-builder::modals.quick-start-guide'))
                ->modalWidth('4xl'),
        ];
    }

    protected function getStats(): array
    {
        return [
            'modules' => ModuleProject::count(),
            'active_modules' => ModuleProject::where('enabled', true)->count(),
            'tables' => \Modules\ModuleBuilder\Models\ModuleTable::count(),
            'fields' => \Modules\ModuleBuilder\Models\ModuleField::count(),
        ];
    }

    protected function getViewData(): array
    {
        $stats = $this->getStats();
        return [
            'recentModules' => ModuleProject::latest()->limit(5)->get(),
            'totalModules' => ModuleProject::count(),
            'activeModules' => ModuleProject::where('enabled', true)->count(),
            'stats' => $stats,
        ];
    }
}
