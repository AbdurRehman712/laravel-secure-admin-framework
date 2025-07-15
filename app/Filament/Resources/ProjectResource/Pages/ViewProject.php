<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('workspace')
                ->label('Open Workspace')
                ->icon('heroicon-o-computer-desktop')
                ->color('success')
                ->url(fn (): string => route('filament.admin.pages.project-workspace', ['project' => $this->getRecord()->id])),
        ];
    }


}
