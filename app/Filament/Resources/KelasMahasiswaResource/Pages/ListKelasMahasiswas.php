<?php

namespace App\Filament\Resources\KelasMahasiswaResource\Pages;

use App\Filament\Resources\KelasMahasiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKelasMahasiswas extends ListRecords
{
    protected static string $resource = KelasMahasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
