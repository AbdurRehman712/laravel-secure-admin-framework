<?php

namespace Modules\CmsEditor\app\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Modules\CmsEditor\app\Filament\Resources\PageResource\Pages;
use Modules\CmsEditor\app\Models\Layout;
use Modules\CmsEditor\app\Models\Page;
use UnitEnum;
use BackedEnum;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static string | UnitEnum | null $navigationGroup = 'Content Management';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $state, callable $set) => $set('slug', str()->slug($state))),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Select::make('layout')
                    ->options(function () {
                        return Layout::query()->pluck('name', 'slug')->toArray();
                    })
                    ->default('default')
                    ->required(),

                Select::make('parent_id')
                    ->label('Parent Page')
                    ->relationship('parent', 'title')
                    ->nullable()
                    ->searchable(),
                
                Repeater::make('contentSections')
                    ->relationship()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Select::make('type')
                            ->options([
                                'text' => 'Text',
                                'html' => 'HTML',
                                'image' => 'Image',
                                'gallery' => 'Gallery',
                                'video' => 'Video',
                            ])
                            ->default('text')
                            ->required(),

                        RichEditor::make('content')
                            ->columnSpanFull()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('cms-content'),
                    ])
                    ->orderable('order')
                    ->defaultItems(1)
                    ->columnSpanFull(),
                
                TextInput::make('meta_title')
                    ->maxLength(255),

                TextInput::make('meta_description')
                    ->maxLength(255),

                Toggle::make('is_published')
                    ->label('Published')
                    ->default(false),

                DatePicker::make('published_at')
                    ->label('Publish Date')
                    ->nullable(),

                TextInput::make('order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('parent.title')
                    ->label('Parent')
                    ->sortable(),

                IconColumn::make('is_published')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->relationship('parent', 'title')
                    ->label('Parent Page'),

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published Status')
                    ->placeholder('All Pages')
                    ->trueLabel('Published Pages')
                    ->falseLabel('Draft Pages'),
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
