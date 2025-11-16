<?php

namespace App\Filament\Resources\PtAssignmentResource\Pages;

use App\Filament\Resources\PtAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPtAssignment extends ViewRecord
{
    protected static string $resource = PtAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
