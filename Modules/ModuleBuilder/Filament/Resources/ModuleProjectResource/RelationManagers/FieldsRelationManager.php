<?php

namespace Modules\ModuleBuilder\Filament\Resources\ModuleProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class FieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'allFields';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $label = 'All Fields';

    protected static ?string $title = 'Module Fields';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('table_id')
                    ->label('Table')
                    ->relationship('table', 'name')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('table_id', $state)),
                
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('email')
                    ->helperText('Database column name (snake_case)'),
                
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'string' => 'String',
                        'text' => 'Text',
                        'integer' => 'Integer',
                        'decimal' => 'Decimal',
                        'boolean' => 'Boolean',
                        'date' => 'Date',
                        'datetime' => 'DateTime',
                        'json' => 'JSON',
                        'file' => 'File',
                    ])
                    ->default('string'),
                
                Forms\Components\TextInput::make('length')
                    ->numeric()
                    ->placeholder('255')
                    ->helperText('For string/varchar fields'),
                
                Forms\Components\TextInput::make('display_name')
                    ->maxLength(255)
                    ->placeholder('Email Address')
                    ->helperText('Human readable label'),
                
                Forms\Components\Toggle::make('nullable')
                    ->label('Nullable')
                    ->helperText('Allow NULL values')
                    ->default(false),
                
                Forms\Components\Toggle::make('unique')
                    ->label('Unique')
                    ->helperText('Enforce unique constraint')
                    ->default(false),
                
                Forms\Components\TextInput::make('default_value')
                    ->maxLength(255)
                    ->placeholder('default_value')
                    ->helperText('Default value for the field'),
                
                Forms\Components\Toggle::make('table_column')
                    ->label('Show in Table')
                    ->helperText('Display this field in data tables')
                    ->default(true),
                
                Forms\Components\Toggle::make('table_searchable')
                    ->label('Searchable')
                    ->helperText('Allow searching by this field')
                    ->default(false),
                
                Forms\Components\Toggle::make('table_sortable')
                    ->label('Sortable')
                    ->helperText('Allow sorting by this field')
                    ->default(false),
                
                Forms\Components\Select::make('form_component')
                    ->label('Form Component')
                    ->options([
                        'TextInput' => 'Text Input',
                        'Textarea' => 'Textarea',
                        'Select' => 'Select',
                        'Toggle' => 'Toggle',
                        'DatePicker' => 'Date Picker',
                        'DateTimePicker' => 'DateTime Picker',
                        'FileUpload' => 'File Upload',
                        'RichEditor' => 'Rich Editor',
                        'KeyValue' => 'Key Value',
                    ])
                    ->helperText('Leave empty to auto-detect'),
                
                Forms\Components\Textarea::make('validation_rules')
                    ->maxLength(1000)
                    ->rows(3)
                    ->placeholder('required|email|max:255')
                    ->helperText('Laravel validation rules'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('table.name')
                    ->label('Table')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Field Name')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('display_name')
                    ->label('Display Name')
                    ->sortable()
                    ->searchable()
                    ->limit(30),
                Tables\Columns\IconColumn::make('nullable')
                    ->boolean()
                    ->label('Nullable'),
                Tables\Columns\IconColumn::make('unique')
                    ->boolean()
                    ->label('Unique'),
                Tables\Columns\IconColumn::make('table_column')
                    ->boolean()
                    ->label('In Table'),
                Tables\Columns\TextColumn::make('form_component')
                    ->label('Form Component')
                    ->badge()
                    ->color('success')
                    ->default('Auto'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('table_id')
                    ->relationship('table', 'name')
                    ->label('Table'),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'string' => 'String',
                        'text' => 'Text',
                        'integer' => 'Integer',
                        'decimal' => 'Decimal',
                        'boolean' => 'Boolean',
                        'date' => 'Date',
                        'datetime' => 'DateTime',
                        'json' => 'JSON',
                        'file' => 'File',
                    ]),
                Tables\Filters\TernaryFilter::make('nullable'),
                Tables\Filters\TernaryFilter::make('unique'),
                Tables\Filters\TernaryFilter::make('table_column')
                    ->label('Show in Table'),
            ])
            ->headerActions([
                // Action temporarily disabled for Filament v4 beta
            ])
            ->actions([
                // Actions temporarily disabled for Filament v4 beta
            ])
            ->bulkActions([
                // Bulk actions temporarily disabled for Filament v4 beta
            ]);
    }
}
