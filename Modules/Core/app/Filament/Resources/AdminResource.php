<?php

namespace Modules\Core\app\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Modules\Core\app\Filament\Resources\AdminResource\Pages;
use Modules\Core\app\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Filament\Concerns\HasModulePermissions;

class AdminResource extends Resource
{
    use HasModulePermissions;
    protected static ?string $model = Admin::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'Admins';
    
    protected static ?string $modelLabel = 'Admin';
    
    protected static ?string $pluralModelLabel = 'Admins';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->minLength(8)
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->label('Password'),
                CheckboxList::make('roles')
                    ->label('Roles')
                    ->options(Role::where('guard_name', 'admin')->pluck('name', 'id')->toArray())
                    ->columns(2)
                    ->bulkToggleable()
                    ->gridDirection('row'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(',')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
            'edit' => Pages\EditAdmin::route('/{record}/edit'),
        ];
    }
}
