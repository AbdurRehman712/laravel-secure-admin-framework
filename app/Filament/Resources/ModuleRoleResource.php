<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use App\Filament\Resources\ModuleRoleResource\Pages;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Services\ModulePermissionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ModuleRoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-check';
    
    protected static ?string $navigationLabel = 'Module Roles';
    protected static ?string $modelLabel = 'Role';
    
    protected static ?string $pluralModelLabel = 'Roles';

    // Add authorization methods
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_module_role') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->can('view_module_role') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_module_role') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_module_role') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_module_role') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('delete_any_module_role') ?? false;
    }    public static function form(Schema $schema): Schema
    {
        $baseFields = [
            TextInput::make('name')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255)
                ->label('Role Name')
                ->helperText('Use descriptive names like "Content Manager", "Admin", etc.'),
            
            Select::make('guard_name')
                ->options([
                    'admin' => 'Admin Guard',
                    'web' => 'Web Guard (Public Users)'
                ])
                ->required()
                ->default('admin')
                ->label('Guard')
                ->helperText('Choose which user type this role applies to'),
        ];        $permissionFields = self::getModulePermissionsField();
        $allFields = array_merge($baseFields, $permissionFields);
        
        return $schema->schema($allFields);
    }

    public static function getModulePermissionsField()
    {
        $modules = ModulePermissionService::getModulesWithPermissions();
        
        // Get only permissions that exist in the database for admin guard
        $existingPermissions = Permission::where('guard_name', 'admin')->pluck('name')->toArray();
        
        $fields = [];
        
        foreach ($modules as $moduleName => $permissions) {
            $modulePermissions = [];
            
            foreach ($permissions as $permission) {
                if (in_array($permission['name'], $existingPermissions)) {
                    $modulePermissions[$permission['name']] = $permission['display_name'];
                }
            }              if (!empty($modulePermissions)) {                $fields[] = CheckboxList::make("{$moduleName}_permissions")
                    ->label("📦 {$moduleName} Module Permissions")
                    ->helperText("Select permissions for {$moduleName} module functionality")
                    ->options($modulePermissions)
                    ->columns(2)
                    ->gridDirection('row')
                    ->bulkToggleable();
            }}
        
        // Add a simple hidden field for now
        $fields[] = Hidden::make('permissions')
            ->default([]);
        
        return $fields;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Role Name'),
                    
                Tables\Columns\BadgeColumn::make('guard_name')
                    ->label('Guard')
                    ->colors([
                        'primary' => 'admin',
                        'success' => 'web',
                    ]),
                    
                Tables\Columns\TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions')
                    ->badge()
                    ->color('warning'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),            ])
            ->filters([
                Tables\Filters\SelectFilter::make('guard_name')
                    ->label('Guard')
                    ->options([
                        'admin' => 'Admin Guard',
                        'web' => 'Web Guard',
                    ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        // Only show roles for admin guard in admin panel
        return parent::getEloquentQuery()->where('guard_name', 'admin');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModuleRoles::route('/'),
            'create' => Pages\CreateModuleRole::route('/create'),
            'edit' => Pages\EditModuleRole::route('/{record}/edit'),
        ];
    }
}
