<?php

namespace App\Filament\Resources\PtAssignmentResource\Pages;

use App\Filament\Resources\PtAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPtAssignments extends ListRecords
{
    protected static string $resource = PtAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
