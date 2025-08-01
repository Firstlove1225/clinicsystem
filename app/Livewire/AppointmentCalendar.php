<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Appointment; // ต้อง import Model Appointment
use Carbon\Carbon; // สำหรับจัดการวันที่

class AppointmentCalendar extends Component
{
    public $events = [];

    public function mount()
    {
        $this->loadEvents();
    }

    private function loadEvents()
    {
        $appointments = Appointment::with(['patient', 'doctor'])
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->get();

        $this->events = $appointments->map(function ($appointment) {
            // นี่คือส่วนที่สำคัญที่ต้องแก้ไข
            // หาก appointment_date ถูก cast เป็น Carbon/Date object แล้ว
            // และ appointment_time ถูก cast เป็น Carbon/Time object แล้ว
            // เราสามารถรวมมันเข้าด้วยกันโดยตรง

            // ให้ใช้ appointment->appointment_date (ที่เป็น Carbon Date)
            // และกำหนดเวลาจาก appointment->appointment_time
            $startDateTime = $appointment->appointment_date->setTime(
                $appointment->appointment_time->hour,
                $appointment->appointment_time->minute,
                $appointment->appointment_time->second
            );

            $endDateTime = (clone $startDateTime)->addMinutes(30);

            $color = '';
            switch ($appointment->status) {
                case 'pending':
                    $color = '#f59e0b'; // สีส้ม (warning)
                    break;
                case 'confirmed':
                    $color = '#10b981'; // สีเขียว (success)
                    break;
                case 'in_progress':
                    $color = '#3b82f6'; // สีน้ำเงิน (primary)
                    break;
                case 'completed':
                    $color = '#6b7280'; // สีเทา (secondary/info)
                    break;
                case 'cancelled':
                    $color = '#ef4444'; // สีแดง (danger)
                    break;
                default:
                    $color = '#6b7280'; // สีเทา default
            }

            return [
                'id' => $appointment->id,
                'title' => "HN: {$appointment->patient->hn} - {$appointment->patient->first_name} ({$appointment->doctor->first_name})",
                'start' => $startDateTime->toIso8601String(),
                'end' => $endDateTime->toIso8601String(),
                'url' => route('filament.admin.resources.appointments.edit', $appointment->id),
                'color' => $color,
                'extendedProps' => [
                    'reason' => $appointment->reason,
                    'status' => $appointment->status,
                ],
            ];
        })->toArray();
    }
    public function render()
    {
        return view('livewire.appointment-calendar');
    }
}
