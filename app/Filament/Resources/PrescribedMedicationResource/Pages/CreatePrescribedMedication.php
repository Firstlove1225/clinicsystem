<?php

namespace App\Filament\Resources\PrescribedMedicationResource\Pages;

use App\Filament\Resources\PrescribedMedicationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePrescribedMedication extends CreateRecord
{
    protected static string $resource = PrescribedMedicationResource::class;
}
