<?php

namespace App\Filament\Resources\MedicationStockResource\Pages;

use App\Filament\Resources\MedicationStockResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMedicationStock extends EditRecord
{
    protected static string $resource = MedicationStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
