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
use Modules\ShopModule\Models\Product;
use Modules\ShopModule\Filament\Resources\ProductResource\Pages;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'ShopModule';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_product') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_product') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_product') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_product') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_product') ?? false;
    }    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('delete_any_product') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('sku')
                    ->maxLength(255),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\TextInput::make('stock_quantity')
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->label('Category'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('USD'),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU'),
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
            'index' => Pages\ListProduct::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
