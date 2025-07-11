<?php

namespace Modules\Shop\app\Filament\Resources;

use Modules\Shop\app\Models\Order;
use Modules\Shop\app\Filament\Resources\OrderResource\Pages;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;


class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static \UnitEnum|string|null $navigationGroup = 'Shop';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_order') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_order') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_order') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_order') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_order') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('order_number')->required()->maxLength(50),
            TextInput::make('customer_name')->required()->maxLength(255),
            TextInput::make('customer_email')->email()->required(),
            TextInput::make('customer_phone')->maxLength(20),
            TextInput::make('subtotal')->numeric()->step(0.01)->required(),
            TextInput::make('tax_amount')->numeric()->step(0.01),
            TextInput::make('shipping_amount')->numeric()->step(0.01),
            TextInput::make('total_amount')->numeric()->step(0.01)->required(),
            Select::make('status')->options(['pending' => 'Pending', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled', 'refunded' => 'Refunded'])->required(),
            Select::make('payment_status')->options(['pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed', 'refunded' => 'Refunded'])->required(),
            TextInput::make('payment_method')->maxLength(50),
            Textarea::make('notes')->rows(3),
            DateTimePicker::make('shipped_at')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->toggleable(),
                TextColumn::make('order_number')->sortable()->toggleable(),
                TextColumn::make('customer_name')->sortable()->toggleable(),
                TextColumn::make('customer_email')->searchable()->sortable()->toggleable(),
                TextColumn::make('customer_phone')->sortable()->toggleable(),
                TextColumn::make('billing_address')->sortable()->toggleable(),
                TextColumn::make('shipping_address')->sortable()->toggleable(),
                TextColumn::make('subtotal')->money('USD')->sortable()->toggleable(),
                TextColumn::make('tax_amount')->money('USD')->sortable()->toggleable(),
                TextColumn::make('shipping_amount')->money('USD')->sortable()->toggleable(),
                TextColumn::make('total_amount')->money('USD')->sortable()->toggleable(),
                TextColumn::make('status')->badge()->toggleable(),
                TextColumn::make('payment_status')->badge()->toggleable(),
                TextColumn::make('payment_method')->sortable()->toggleable(),
                TextColumn::make('notes')->sortable()->toggleable(),
                TextColumn::make('shipped_at')->dateTime()->sortable()->toggleable(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('delete')
                        ->requiresConfirmation()
                        ->action(fn (\Illuminate\Database\Eloquent\Collection $records) => $records->each->delete()),
                    BulkAction::make('export')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            // Simple CSV export functionality
                            $filename = 'order_export_' . now()->format('Y_m_d_H_i_s') . '.csv';
                            $headers = [
                                'Content-Type' => 'text/csv',
                                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                            ];

                            $callback = function() use ($records) {
                                $file = fopen('php://output', 'w');

                                // Add CSV headers
                                if ($records->isNotEmpty()) {
                                    $firstRecord = $records->first();
                                    $headers = [];
                                    foreach ($firstRecord->toArray() as $key => $value) {
                                        $headers[] = $key;
                                    }
                                    fputcsv($file, $headers);
                                }

                                // Add data rows
                                foreach ($records as $record) {
                                    $row = [];
                                    foreach ($record->toArray() as $key => $value) {
                                        // Handle array/object values (like relationships)
                                        if (is_array($value) || is_object($value)) {
                                            $row[] = is_array($value) ? implode(', ', $value) : (string) $value;
                                        } else {
                                            $row[] = $value;
                                        }
                                    }
                                    fputcsv($file, $row);
                                }

                                fclose($file);
                            };

                            return response()->stream($callback, 200, $headers);
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}