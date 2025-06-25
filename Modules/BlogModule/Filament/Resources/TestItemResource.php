<?php

namespace Modules\BlogModule\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Schemas\Schema;
use Modules\BlogModule\Models\TestItem;
use Modules\BlogModule\Filament\Resources\TestItemResource\Pages;

class TestItemResource extends Resource
{
    protected static ?string $model = TestItem::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'BlogModule';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_test_item') ?? true; // Changed to true for testing
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_test_item') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_test_item') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_test_item') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_test_item') ?? false;
    }    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('delete_any_test_item') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(false),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('price')
                    ->money('USD'),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
            ])            ->filters([
                //
            ])            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestItem::route('/'),
            'create' => Pages\CreateTestItem::route('/create'),
            'edit' => Pages\EditTestItem::route('/{record}/edit'),
        ];
    }
}
