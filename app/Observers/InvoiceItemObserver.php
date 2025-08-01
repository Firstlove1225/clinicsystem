<?php

namespace App\Observers;

use App\Models\InvoiceItem;
use App\Models\Invoice; // Import Invoice Model

class InvoiceItemObserver
{
    /**
     * Handle the InvoiceItem "created" event.
     *
     * @param  \App\Models\InvoiceItem  $invoiceItem
     * @return void
     */
    public function created(InvoiceItem $invoiceItem): void
    {
        $this->updateInvoiceTotal($invoiceItem->invoice);
    }

    /**
     * Handle the InvoiceItem "updated" event.
     *
     * @param  \App\Models\InvoiceItem  $invoiceItem
     * @return void
     */
    public function updated(InvoiceItem $invoiceItem): void
    {
        $this->updateInvoiceTotal($invoiceItem->invoice);
    }

    /**
     * Handle the InvoiceItem "deleted" event.
     *
     * @param  \App\Models\InvoiceItem  $invoiceItem
     * @return void
     */
    public function deleted(InvoiceItem $invoiceItem): void
    {
        $this->updateInvoiceTotal($invoiceItem->invoice);
    }

    /**
     * Calculate and update the total amount of the associated invoice.
     */
    protected function updateInvoiceTotal(Invoice $invoice): void
    {
        // Sum all sub_total from related invoice items
        $total = $invoice->invoiceItems()->sum('sub_total');
        $invoice->total_amount = $total;
        $invoice->save();

        // อัปเดตสถานะใบแจ้งหนี้ตามยอดที่ชำระ
        if ($invoice->paid_amount >= $invoice->total_amount && $invoice->total_amount > 0) {
            $invoice->status = 'paid';
        } elseif ($invoice->paid_amount > 0 && $invoice->paid_amount < $invoice->total_amount) {
            $invoice->status = 'partial_paid';
        } elseif ($invoice->paid_amount == 0 && $invoice->total_amount > 0) {
            $invoice->status = 'pending';
        } else {
            // กรณี total_amount เป็น 0
            $invoice->status = 'pending';
        }
        $invoice->save();
    }

    // เมธอดอื่นๆ ที่ไม่ได้ใช้ในตอนนี้
    public function restored(InvoiceItem $invoiceItem): void {}
    public function forceDeleted(InvoiceItem $invoiceItem): void {}
}
