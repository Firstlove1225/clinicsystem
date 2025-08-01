<?php

namespace App\Filament\Resources\MedicationStockResource\Pages;

use App\Filament\Resources\MedicationStockResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMedicationStocks extends ListRecords
{
    protected static string $resource = MedicationStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
