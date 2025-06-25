<?php

namespace Modules\ModuleBuilder\Filament\Resources;

use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Modules\ModuleBuilder\Models\ModuleProject;
use Modules\ModuleBuilder\Filament\Resources\ModuleProjectResource\Pages;
use Modules\ModuleBuilder\Filament\Resources\ModuleProjectResource\RelationManagers;

class ModuleProjectResource extends Resource
{
    protected static ?string $model = ModuleProject::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cube';
    
    protected static ?string $navigationLabel = 'Module Builder';
    
    // protected static ?string $navigationGroup = 'Development';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., BlogModule')
                    ->helperText('Module name (will be used as directory name)'),
                
                Forms\Components\TextInput::make('namespace')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., Modules\\BlogModule')
                    ->helperText('PHP namespace for the module'),
                
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull()
                    ->placeholder('Brief description of what this module does...'),
                
                Forms\Components\TextInput::make('version')
                    ->default('1.0.0')
                    ->required()
                    ->maxLength(20)
                    ->placeholder('1.0.0'),
                
                Forms\Components\TextInput::make('author_name')
                    ->maxLength(255)
                    ->placeholder('John Doe'),
                
                Forms\Components\TextInput::make('author_email')
                    ->email()
                    ->maxLength(255)
                    ->placeholder('john@example.com'),
                
                Forms\Components\Toggle::make('has_admin_panel')
                    ->label('Admin Panel Integration')
                    ->helperText('Generate Filament resources for admin panel')
                    ->default(true),
                
                Forms\Components\Toggle::make('has_api')
                    ->label('API Endpoints')
                    ->helperText('Generate API controllers and routes')
                    ->default(false),
                
                Forms\Components\Toggle::make('has_web_routes')
                    ->label('Web Routes')
                    ->helperText('Generate web controllers and routes')
                    ->default(false),
                
                Forms\Components\Toggle::make('enabled')
                    ->label('Enable Module')
                    ->helperText('Whether the module should be active')
                    ->default(false),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('namespace')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('version')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('author_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'building' => 'warning',
                        'built' => 'success',
                        'error' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('enabled')
                    ->boolean(),
                Tables\Columns\TextColumn::make('tables_count')
                    ->counts('tables')
                    ->label('Tables')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'building' => 'Building',
                        'built' => 'Built',
                        'error' => 'Error',
                    ]),
                Tables\Filters\TernaryFilter::make('enabled'),
            ])
            ->actions([
                // Actions will be added with correct Filament v3 syntax
            ])
            ->bulkActions([
                // Bulk actions will be added with correct Filament v3 syntax  
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TablesRelationManager::class,
            RelationManagers\FieldsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModuleProjects::route('/'),
            'create' => Pages\CreateModuleProject::route('/create'),
            'edit' => Pages\EditModuleProject::route('/{record}/edit'),
        ];
    }
}