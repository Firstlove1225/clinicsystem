<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // เพิ่ม use สำหรับ BelongsTo
use Illuminate\Database\Eloquent\Relations\HasMany; // เพิ่ม use สำหรับ HasMany

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'patient_id',
        'consultation_id',
        'total_amount',
        'paid_amount',
        'status',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    /**
     * Get the patient that owns the invoice.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the consultation that owns the invoice.
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    /**
     * Get the invoice items for the invoice.
     */
    // public function invoiceItems(): HasMany
    // {
    //     return $this->hasMany(InvoiceItem::class);
    // }
}
