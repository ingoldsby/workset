<?php

namespace App\Filament\Resources\PtAssignmentResource\Pages;

use App\Filament\Resources\PtAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPtAssignment extends EditRecord
{
    protected static string $resource = PtAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
