<?php

namespace Modules\CmsEditor\app\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\CmsEditor\app\Filament\Resources\ThemeResource\Pages;
use Modules\CmsEditor\app\Services\ThemeManager;
use Symfony\Component\Yaml\Yaml;
use UnitEnum;
use BackedEnum;
use Illuminate\Support\Facades\Route;

class ThemeResource extends Resource
{
    protected static ?string $model = \Modules\CmsEditor\app\Models\FileTheme::class;
    
    // Try a different slug to see if 'themes' is causing issues
    protected static ?string $slug = 'theme-manager';
    protected static ?string $modelLabel = 'Theme';
    protected static ?string $pluralModelLabel = 'Themes';
    protected static ?string $recordTitleAttribute = 'name';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-swatch';

    protected static string | UnitEnum | null $navigationGroup = 'Content Management';

    public static function getNavigationLabel(): string
    {
        return 'Themes';
    }

    public static function getPluralLabel(): string
    {
        return 'Themes';
    }
    
    public static function getLabel(): string
    {
        return 'Theme';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required(),
                TextInput::make('code')
                    ->required(),
                Textarea::make('description')
                    ->rows(3),
                TextInput::make('author')
                    ->required(),
                TextInput::make('homepage')
                    ->url(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('author')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('editor')
                        ->label('Editor')
                        ->url(fn ($record) => static::getUrl('edit-file', ['theme' => $record->code]))
                        ->icon('heroicon-o-code-bracket')
                        ->color('primary'),
                    Action::make('browse')
                        ->label('Browse')
                        ->url(fn ($record) => static::getUrl('browse', ['record' => $record->code]))
                        ->icon('heroicon-o-folder-open')
                        ->color('success'),
                    Action::make('edit')
                        ->label('Settings')
                        ->url(fn ($record) => static::getUrl('edit', ['record' => $record->code]))
                        ->icon('heroicon-o-cog-6-tooth'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListThemes::route('/'),
            'create' => Pages\CreateTheme::route('/create'),
            'edit' => Pages\EditTheme::route('/{record}/edit'),
            'browse' => Pages\BrowseTheme::route('/{record}/browse'),
            'edit-file' => Pages\EditThemeFile::route('/{theme}/editor/{file?}'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Return a dummy query since we override getTableRecords in ListThemes
        return \Modules\CmsEditor\app\Models\FileTheme::query();
    }

}
