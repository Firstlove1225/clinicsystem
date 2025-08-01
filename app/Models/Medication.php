<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // เพิ่ม use สำหรับ HasMany

class Medication extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'generic_name',
        'dosage_form',
        'strength',
        'description',
        'price_per_unit',
        'unit',
    ];

    protected $casts = [
        'price_per_unit' => 'decimal:2', // Cast เป็น decimal 2 ตำแหน่ง สำหรับราคา
    ];

    /**
     * Get the medication stocks associated with the medication.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(MedicationStock::class); // จะใช้ในขั้นตอนต่อไป
    }
}
