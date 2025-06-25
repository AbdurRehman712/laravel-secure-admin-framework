<?php

namespace Modules\ShopModule\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Schemas\Schema;
use Modules\ShopModule\Models\Category;
use Modules\ShopModule\Filament\Resources\CategoryResource\Pages;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'ShopModule';

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
    }    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('delete_any_category') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('status')
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Category Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('URL Slug'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
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
            'index' => Pages\ListCategory::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
