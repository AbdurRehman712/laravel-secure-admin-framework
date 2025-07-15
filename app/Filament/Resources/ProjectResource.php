<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Modules\Core\app\Models\Admin;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-folder';

    protected static \UnitEnum|string|null $navigationGroup = 'AI Platform';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(debounce: 500)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $currentSlug = $get('slug');
                        if (empty($currentSlug) || $currentSlug === Str::slug($get('name'))) {
                            $set('slug', Str::slug($state));
                        }
                    }),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Auto-generated from project name, but you can customize it'),

                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),

                Select::make('status')
                    ->options(Project::getStatuses())
                    ->default(Project::STATUS_PLANNING)
                    ->required(),

                Select::make('created_by')
                    ->label('Project Owner')
                    ->options(Admin::pluck('name', 'id'))
                    ->default(auth('admin')->id())
                    ->required()
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Project::STATUS_PLANNING => 'gray',
                        Project::STATUS_DEVELOPMENT => 'warning',
                        Project::STATUS_REVIEW => 'info',
                        Project::STATUS_COMPLETED => 'success',
                        Project::STATUS_ARCHIVED => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Owner')
                    ->sortable(),

                Tables\Columns\TextColumn::make('teamMembers_count')
                    ->label('Team Size')
                    ->counts('teamMembers')
                    ->sortable(),

                Tables\Columns\TextColumn::make('workspaceContent_count')
                    ->label('Content Items')
                    ->counts('workspaceContent')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(Project::getStatuses()),

                Tables\Filters\SelectFilter::make('created_by')
                    ->label('Owner')
                    ->options(Admin::pluck('name', 'id')),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('workspace')
                        ->label('Open Workspace')
                        ->icon('heroicon-o-computer-desktop')
                        ->color('success')
                        ->url(fn (Project $record): string => "/admin/project-workspace?project={$record->id}"),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('delete')
                        ->label('Delete Selected')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->delete()),
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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
