<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescribedMedication extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'medication_id',
        'quantity',
        'unit',
        'instructions',
    ];

    /**
     * Get the consultation that owns the prescribed medication.
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    /**
     * Get the medication that was prescribed.
     */
    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }
}
