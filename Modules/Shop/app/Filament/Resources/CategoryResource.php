<?php

namespace Modules\Shop\app\Filament\Resources;

use Modules\Shop\app\Models\Category;
use Modules\Shop\app\Filament\Resources\CategoryResource\Pages;
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


class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static \UnitEnum|string|null $navigationGroup = 'Shop';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_category') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_category') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_category') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_category') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_category') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('slug')->required()->helperText('Auto-generated from name, but you can edit it')->placeholder('Will be auto-generated'),
            Textarea::make('description')->rows(3),
            FileUpload::make('image')->image(),
            Toggle::make('active'),
            TextInput::make('sort_order')->numeric()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->toggleable(),
                TextColumn::make('name')->searchable()->sortable()->toggleable(),
                TextColumn::make('slug')->searchable()->sortable()->toggleable(),
                TextColumn::make('description')->sortable()->toggleable(),
                TextColumn::make('image')->sortable()->toggleable(),
                BooleanColumn::make('active')->toggleable(),
                TextColumn::make('sort_order')->sortable()->toggleable(),
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
                            $filename = 'category_export_' . now()->format('Y_m_d_H_i_s') . '.csv';
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}