<?php

namespace App\Filament\Resources\SoalTugasResource\Pages;

use App\Filament\Resources\SoalTugasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSoalTugas extends ListRecords
{
    protected static string $resource = SoalTugasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
