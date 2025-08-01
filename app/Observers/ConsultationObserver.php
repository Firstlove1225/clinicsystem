<?php

namespace App\Observers;

use App\Models\Consultation;

class ConsultationObserver
{
    public function created(Consultation $consultation): void
    {
        if ($consultation->queue_id) {
            $queue = \App\Models\Queue::find($consultation->queue_id);
            if ($queue && $queue->status !== 'completed') {
                $queue->status = 'completed';
                $queue->save();
            }
        }
        if ($consultation->appointment_id) {
            $appointment = \App\Models\Appointment::find($consultation->appointment_id);
            if ($appointment && $appointment->status !== 'completed') {
                $appointment->status = 'completed';
                $appointment->save();
            }
        }
    }

    /**
     * Handle the Consultation "updated" event.
     */
    public function updated(Consultation $consultation): void
    {
        //
    }

    /**
     * Handle the Consultation "deleted" event.
     */
    public function deleted(Consultation $consultation): void
    {
        //
    }

    /**
     * Handle the Consultation "restored" event.
     */
    public function restored(Consultation $consultation): void
    {
        //
    }

    /**
     * Handle the Consultation "force deleted" event.
     */
    public function forceDeleted(Consultation $consultation): void
    {
        //
    }
}
