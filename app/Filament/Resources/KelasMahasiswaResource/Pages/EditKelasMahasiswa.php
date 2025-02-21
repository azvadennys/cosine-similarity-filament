<?php

namespace App\Filament\Resources\KelasMahasiswaResource\Pages;

use App\Filament\Resources\KelasMahasiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKelasMahasiswa extends EditRecord
{
    protected static string $resource = KelasMahasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
