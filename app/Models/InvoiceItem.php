<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'item_type',
        'item_id',
        'item_name',
        'quantity',
        'unit_price',
        'sub_total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'sub_total' => 'decimal:2',
    ];

    /**
     * Get the invoice that owns the invoice item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the parent item (Service or Medication).
     * This is a polymorphic relationship, but handled manually here.
     */
    public function item()
    {
        if ($this->item_type === 'service') {
            return $this->belongsTo(Service::class, 'item_id');
        } elseif ($this->item_type === 'medication') {
            return $this->belongsTo(Medication::class, 'item_id');
        }
        return null;
    }
}
