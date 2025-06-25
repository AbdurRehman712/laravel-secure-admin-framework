<?php

namespace Modules\ModuleBuilder\Filament\Resources;

use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Modules\ModuleBuilder\Models\ModuleField;
use Modules\ModuleBuilder\Filament\Resources\ModuleFieldResource\Pages;

class ModuleFieldResource extends Resource
{
    protected static ?string $model = ModuleField::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-variable';
    
    protected static ?string $navigationLabel = 'Fields';
    
    protected static \UnitEnum|string|null $navigationGroup = 'Module Builder';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('table_id')
                    ->label('Table')
                    ->relationship('table', 'name')
                    ->required()
                    ->preload()
                    ->searchable(),
                
                Forms\Components\TextInput::make('name')
                    ->label('Field Name')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Database column name (e.g., first_name, email)'),
                
                Forms\Components\TextInput::make('label')
                    ->label('Display Label')
                    ->maxLength(255)
                    ->helperText('Human-readable label for forms'),
                
                Forms\Components\Select::make('type')
                    ->label('Field Type')
                    ->required()
                    ->options([
                        'string' => 'String',
                        'text' => 'Text',
                        'integer' => 'Integer',
                        'bigInteger' => 'Big Integer',
                        'decimal' => 'Decimal',
                        'boolean' => 'Boolean',
                        'date' => 'Date',
                        'datetime' => 'DateTime',
                        'timestamp' => 'Timestamp',
                        'json' => 'JSON',
                        'enum' => 'Enum',
                        'foreignId' => 'Foreign ID',
                    ])
                    ->searchable(),
                    
                Forms\Components\TextInput::make('length')
                    ->label('Length')
                    ->numeric()
                    ->helperText('For string/varchar fields'),
                
                Forms\Components\Toggle::make('nullable')
                    ->label('Nullable')
                    ->helperText('Allow NULL values'),
                
                Forms\Components\Toggle::make('unique')
                    ->label('Unique')
                    ->helperText('Unique constraint'),
                
                Forms\Components\Select::make('filament_type')
                    ->label('Filament Component')
                    ->options([
                        'TextInput' => 'Text Input',
                        'Textarea' => 'Textarea',
                        'Select' => 'Select',
                        'Toggle' => 'Toggle',
                        'DatePicker' => 'Date Picker',
                        'DateTimePicker' => 'DateTime Picker',
                        'FileUpload' => 'File Upload',
                        'RichEditor' => 'Rich Editor',
                        'Hidden' => 'Hidden',
                    ]),
                
                Forms\Components\TextInput::make('validation_rules')
                    ->label('Validation Rules')
                    ->maxLength(500)
                    ->helperText('Laravel validation rules (e.g., required|min:3)'),
                
                Forms\Components\Textarea::make('description')
                    ->label('Field Description')
                    ->rows(3)
                    ->maxLength(1000)
                    ->helperText('Optional description of this field'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Field Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                
                Tables\Columns\TextColumn::make('table.name')
                    ->label('Table')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color('secondary'),
                
                Tables\Columns\TextColumn::make('filament_type')
                    ->label('Filament Type')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\IconColumn::make('nullable')
                    ->label('Nullable')
                    ->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-mark'),
                
                Tables\Columns\IconColumn::make('unique')
                    ->label('Unique')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('table_id')
                    ->label('Table')
                    ->relationship('table', 'name')
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('type')
                    ->label('Field Type')
                    ->options([
                        'string' => 'String',
                        'text' => 'Text',
                        'integer' => 'Integer',
                        'boolean' => 'Boolean',
                        'date' => 'Date',
                        'datetime' => 'DateTime',
                    ]),
                
                Tables\Filters\TernaryFilter::make('nullable')
                    ->label('Nullable'),
                
                Tables\Filters\TernaryFilter::make('unique')
                    ->label('Unique'),
            ])
            ->actions([
            ])
            ->bulkActions([
            ])
            ->defaultSort('table_id', 'asc');
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModuleFields::route('/'),
            'create' => Pages\CreateModuleField::route('/create'),
            'edit' => Pages\EditModuleField::route('/{record}/edit'),
        ];
    }
}
