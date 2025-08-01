<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // เพิ่ม use สำหรับ BelongsTo

class MedicationStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'medication_id',
        'lot_number',
        'quantity',
        'expiry_date',
        'location',
    ];

    protected $casts = [
        'expiry_date' => 'date', // Cast วันหมดอายุเป็น Carbon date object
    ];

    /**
     * Get the medication that owns the stock.
     */
    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }
}
