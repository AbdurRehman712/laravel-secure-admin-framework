<?php

namespace Modules\ModuleBuilder\Filament\Resources\ModuleProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class TablesRelationManager extends RelationManager
{
    protected static string $relationship = 'tables';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('posts')
                    ->helperText('Database table name (snake_case)'),
                
                Forms\Components\TextInput::make('display_name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Posts')
                    ->helperText('Human readable name'),
                
                Forms\Components\TextInput::make('model_name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Post')
                    ->helperText('Eloquent model class name'),
                
                Forms\Components\TextInput::make('controller_name')
                    ->maxLength(255)
                    ->placeholder('PostController')
                    ->helperText('Controller class name (optional)'),
                
                Forms\Components\TextInput::make('resource_name')
                    ->maxLength(255)
                    ->placeholder('PostResource')
                    ->helperText('Filament resource class name (optional)'),
                
                Forms\Components\TextInput::make('migration_name')
                    ->maxLength(255)
                    ->placeholder('create_posts_table')
                    ->helperText('Migration file name (optional)'),
                
                Forms\Components\Toggle::make('has_timestamps')
                    ->label('Timestamps')
                    ->helperText('Include created_at and updated_at columns')
                    ->default(true),
                
                Forms\Components\Toggle::make('has_soft_deletes')
                    ->label('Soft Deletes')
                    ->helperText('Include deleted_at column')
                    ->default(false),
                
                Forms\Components\Textarea::make('description')
                    ->maxLength(1000)
                    ->rows(3)
                    ->placeholder('Brief description of this table...'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Table Name')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('display_name')
                    ->label('Display Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('model_name')
                    ->label('Model')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fields_count')
                    ->counts('fields')
                    ->label('Fields')
                    ->badge()
                    ->color('info'),
                Tables\Columns\IconColumn::make('has_timestamps')
                    ->boolean()
                    ->label('Timestamps'),
                Tables\Columns\IconColumn::make('has_soft_deletes')
                    ->boolean()
                    ->label('Soft Deletes'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('has_timestamps'),
                Tables\Filters\TernaryFilter::make('has_soft_deletes'),
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make() // Actions temporarily disabled for Filament v4 beta
            ])
            ->actions([
                // Tables\Actions\EditAction::make(), // Actions temporarily disabled for Filament v4 beta
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([...]) // Actions temporarily disabled for Filament v4 beta
            ]);
    }
}