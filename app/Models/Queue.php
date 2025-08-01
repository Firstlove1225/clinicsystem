<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_id',
        'queue_number',
        'check_in_time',
        'status',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public static function generateNextQueueNumber(): string
    {
        $today = Carbon::today();
        $lastQueue = self::whereDate('created_at', $today)
            ->latest('queue_number')
            ->first();

        $nextNumber = 1;
        if ($lastQueue) {
            $lastNumber = (int) substr($lastQueue->queue_number, -3); // Extract last 3 digits
            $nextNumber = $lastNumber + 1;
        }

        return $today->format('Ymd') . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
