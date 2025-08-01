<?php

namespace App\Observers;

use App\Models\PrescribedMedication;
use App\Models\MedicationStock; // ต้อง import MedicationStock Model

class PrescribedMedicationObserver
{
    /**
     * Handle the PrescribedMedication "created" event.
     *
     * เมื่อมีการบันทึกยาที่สั่งจ่าย ให้หักสต็อกยา
     */
    public function created(PrescribedMedication $prescribedMedication): void
    {
        // ค้นหาสต็อกยาที่เกี่ยวข้อง (อาจจะต้องมี logic เลือก Lot ที่ถูกต้องในอนาคต)
        // สำหรับตอนนี้ เราจะหักจากสต็อกใดๆ ที่มียาตัวนี้อยู่
        $stock = MedicationStock::where('medication_id', $prescribedMedication->medication_id)
            ->where('quantity', '>=', $prescribedMedication->quantity) // ตรวจสอบว่ามีจำนวนพอ
            ->orderBy('expiry_date', 'asc') // เลือก Lot ที่จะหมดอายุก่อน (FIFO-like)
            ->first();

        if ($stock) {
            $stock->quantity -= $prescribedMedication->quantity;
            $stock->save();
            // อาจจะเพิ่ม logic สำหรับการบันทึก transaction หรือแจ้งเตือนเมื่อสต็อกเหลือน้อย
        } else {
            // กรณีสต็อกไม่พอ: คุณอาจจะต้องการเพิ่มระบบแจ้งเตือนหรือบันทึก Log
            // Log::warning("Not enough stock for medication ID: {$prescribedMedication->medication_id} for prescription ID: {$prescribedMedication->id}");
            // หรืออาจจะโยน Exception เพื่อป้องกันการบันทึกยาหากสต็อกไม่พอจริงๆ
        }
    }

    /**
     * Handle the PrescribedMedication "deleted" event.
     *
     * เมื่อมีการลบยาที่สั่งจ่าย ให้คืนสต็อกยา
     */
    public function deleted(PrescribedMedication $prescribedMedication): void
    {
        // ค้นหาสต็อกยาที่เกี่ยวข้องเพื่อคืนจำนวน
        // ในทางปฏิบัติจริง อาจจะต้องมีระบบติดตามว่าสต็อกถูกหักจาก Lot ไหน
        // แต่สำหรับตอนนี้ เราจะคืนกลับไปยัง Lot ที่มีอยู่
        $stock = MedicationStock::where('medication_id', $prescribedMedication->medication_id)
            ->first(); // เลือก Lot แรกที่เจอ

        if ($stock) {
            $stock->quantity += $prescribedMedication->quantity;
            $stock->save();
        }
    }

    // เมธอดอื่นๆ ที่ไม่ได้ใช้ในตอนนี้
    public function updated(PrescribedMedication $prescribedMedication): void {}
    public function restored(PrescribedMedication $prescribedMedication): void {}
    public function forceDeleted(PrescribedMedication $prescribedMedication): void {}
}
