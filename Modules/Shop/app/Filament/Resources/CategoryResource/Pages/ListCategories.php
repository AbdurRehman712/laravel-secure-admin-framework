<?php

namespace Modules\Shop\app\Filament\Resources\CategoryResource\Pages;

use Modules\Shop\app\Filament\Resources\CategoryResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [
            CreateAction::make()
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];

        // Add seeder action if seeder exists
        if (class_exists('Modules\Shop\database\seeders\CategorySeeder')) {
            $actions[] = Action::make('run_seeder')
                ->label('Run Seeder')
                ->icon('heroicon-o-play')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Run Category Seeder')
                ->modalDescription('This will create sample Category records. Are you sure?')
                ->action(function () {
                    try {
                        Artisan::call('db:seed', ['--class' => 'Modules\Shop\database\seeders\CategorySeeder']);

                        Notification::make()
                            ->title('Seeder executed successfully!')
                            ->body('Sample Category records have been created.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Seeder execution failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                });
        }

        // Add factory action if factory exists
        if (class_exists('Modules\Shop\database\factories\CategoryFactory')) {
            $actions[] = Action::make('create_test_data')
                ->label('Create Test Data')
                ->icon('heroicon-o-beaker')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Create Test Category Data')
                ->modalDescription('This will create 10 test Category records using the factory. Are you sure?')
                ->action(function () {
                    try {
                        \Modules\Shop\app\Models\Category::factory()->count(10)->create();

                        Notification::make()
                            ->title('Test data created successfully!')
                            ->body('10 test Category records have been created.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Test data creation failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                });
        }

        return $actions;
    }
}