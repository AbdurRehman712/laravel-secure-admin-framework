<?php

namespace Modules\ModuleBuilder\Filament\Resources;

use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Modules\ModuleBuilder\Models\ModuleTable;
use Modules\ModuleBuilder\Filament\Resources\ModuleTableResource\Pages;
use App\Filament\Concerns\HasModulePermissions;

class ModuleTableResource extends Resource
{
    use HasModulePermissions;

    protected static ?string $model = ModuleTable::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-table-cells';
    
    protected static ?string $navigationLabel = 'Tables';
    
    protected static \UnitEnum|string|null $navigationGroup = 'Module Builder';

    protected static ?int $navigationSort = 2;

    // Authorization methods for permission checking
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_module_table') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_module_table') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_module_table') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_module_table') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_module_table') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('delete_any_module_table') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('project_id')
                    ->label('Module Project')
                    ->relationship('moduleProject', 'name')
                    ->required()
                    ->preload()
                    ->searchable(),
                
                Forms\Components\TextInput::make('name')
                    ->label('Table Name')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Enter the table name (e.g., posts, categories)'),
                
                Forms\Components\TextInput::make('model_name')
                    ->label('Model Name')
                    ->required()
                    ->maxLength(255)
                    ->helperText('The Eloquent model name (e.g., Post, Category)'),
                
                Forms\Components\TextInput::make('migration_name')
                    ->label('Migration Name')
                    ->maxLength(255)
                    ->helperText('Optional: Custom migration name'),
                
                Forms\Components\Toggle::make('has_timestamps')
                    ->label('Timestamps')
                    ->helperText('Include created_at and updated_at columns')
                    ->default(true),
                
                Forms\Components\Toggle::make('has_soft_deletes')
                    ->label('Soft Deletes')
                    ->helperText('Include deleted_at column for soft deletion'),
                
                Forms\Components\Toggle::make('create_filament_resource')
                    ->label('Create Filament Resource')
                    ->helperText('Generate Filament admin resource')
                    ->default(true),
                
                Forms\Components\Textarea::make('description')
                    ->label('Table Description')
                    ->rows(3)
                    ->maxLength(1000)
                    ->helperText('Optional description of what this table stores'),
                
                // Inline Fields Management
                Forms\Components\Repeater::make('fields')
                    ->label('Table Fields')
                    ->relationship('fields')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Field Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('email, first_name, etc.')
                            ->columnSpan(2),
                        
                        Forms\Components\TextInput::make('label')
                            ->label('Display Label')
                            ->maxLength(255)
                            ->placeholder('Email Address, First Name, etc.')
                            ->columnSpan(2),
                        
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
                            ->searchable()
                            ->columnSpan(2),
                        
                        Forms\Components\TextInput::make('length')
                            ->label('Length')
                            ->numeric()
                            ->placeholder('255')
                            ->helperText('For string/varchar fields')
                            ->columnSpan(1),
                        
                        Forms\Components\Toggle::make('nullable')
                            ->label('Nullable')
                            ->helperText('Allow NULL values')
                            ->columnSpan(1),
                        
                        Forms\Components\Toggle::make('unique')
                            ->label('Unique')
                            ->helperText('Unique constraint')
                            ->columnSpan(1),
                        
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
                            ])
                            ->columnSpan(2),
                        
                        Forms\Components\TextInput::make('validation_rules')
                            ->label('Validation Rules')
                            ->maxLength(500)
                            ->placeholder('required|min:3|max:255')
                            ->helperText('Laravel validation rules')
                            ->columnSpan(2),
                    ])
                    ->columns(4)
                    ->defaultItems(0)
                    ->addActionLabel('Add Field')
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'New Field')
                    ->helperText('Add fields to this table. You can always add more fields later by editing the table.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Table Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                
                Tables\Columns\TextColumn::make('moduleProject.name')
                    ->label('Module')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('model_name')
                    ->label('Model')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('fields_count')
                    ->counts('fields')
                    ->label('Fields')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\IconColumn::make('has_timestamps')
                    ->label('Timestamps')
                    ->boolean()
                    ->trueIcon('heroicon-o-clock')
                    ->falseIcon('heroicon-o-x-mark'),
                
                Tables\Columns\IconColumn::make('has_soft_deletes')
                    ->label('Soft Deletes')
                    ->boolean()
                    ->trueIcon('heroicon-o-trash')
                    ->falseIcon('heroicon-o-x-mark'),
                
                Tables\Columns\IconColumn::make('create_filament_resource')
                    ->label('Filament Resource')
                    ->boolean()
                    ->trueIcon('heroicon-o-computer-desktop')
                    ->falseIcon('heroicon-o-x-mark'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Module')
                    ->relationship('moduleProject', 'name')
                    ->preload(),
                
                Tables\Filters\TernaryFilter::make('has_timestamps')
                    ->label('Has Timestamps'),
                
                Tables\Filters\TernaryFilter::make('has_soft_deletes')
                    ->label('Has Soft Deletes'),
                
                Tables\Filters\TernaryFilter::make('create_filament_resource')
                    ->label('Creates Filament Resource'),
            ])
            ->actions([
            ])
            ->bulkActions([
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModuleTables::route('/'),
            'create' => Pages\CreateModuleTable::route('/create'),
            'edit' => Pages\EditModuleTable::route('/{record}/edit'),
        ];
    }
}
