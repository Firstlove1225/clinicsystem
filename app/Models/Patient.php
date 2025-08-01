<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // เพิ่ม use นี้

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'hn',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'address',
        'phone_number',
        'emergency_contact_name',
        'emergency_contact_phone',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    // ... เมธอดอื่นๆ ที่มีอยู่

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }
}
