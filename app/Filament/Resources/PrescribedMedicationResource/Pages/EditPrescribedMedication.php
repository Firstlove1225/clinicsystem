<?php

namespace App\Filament\Resources\PrescribedMedicationResource\Pages;

use App\Filament\Resources\PrescribedMedicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrescribedMedication extends EditRecord
{
    protected static string $resource = PrescribedMedicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
