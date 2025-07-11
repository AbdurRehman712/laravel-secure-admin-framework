<?php

namespace Modules\Shop\app\Filament\Resources\OrderResource\Pages;

use Modules\Shop\app\Filament\Resources\OrderResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [
            CreateAction::make()
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];

        // Add seeder action if seeder exists
        if (class_exists('Modules\Shop\database\seeders\OrderSeeder')) {
            $actions[] = Action::make('run_seeder')
                ->label('Run Seeder')
                ->icon('heroicon-o-play')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Run Order Seeder')
                ->modalDescription('This will create sample Order records. Are you sure?')
                ->action(function () {
                    try {
                        Artisan::call('db:seed', ['--class' => 'Modules\Shop\database\seeders\OrderSeeder']);

                        Notification::make()
                            ->title('Seeder executed successfully!')
                            ->body('Sample Order records have been created.')
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
        if (class_exists('Modules\Shop\database\factories\OrderFactory')) {
            $actions[] = Action::make('create_test_data')
                ->label('Create Test Data')
                ->icon('heroicon-o-beaker')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Create Test Order Data')
                ->modalDescription('This will create 10 test Order records using the factory. Are you sure?')
                ->action(function () {
                    try {
                        \Modules\Shop\app\Models\Order::factory()->count(10)->create();

                        Notification::make()
                            ->title('Test data created successfully!')
                            ->body('10 test Order records have been created.')
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