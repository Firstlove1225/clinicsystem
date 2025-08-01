<?php

namespace App\Providers;

use App\Models\Consultation;
use App\Observers\ConsultationObserver;
use Illuminate\Support\ServiceProvider;
use App\Models\PrescribedMedication; // เพิ่มบรรทัดนี้
use App\Observers\PrescribedMedicationObserver; // เพิ่มบรรทัดนี้
use App\Models\InvoiceItem; // เพิ่มบรรทัดนี้
use App\Observers\InvoiceItemObserver; // เพิ่มบรรทัดนี้


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Consultation::observe(ConsultationObserver::class);
        PrescribedMedication::observe(PrescribedMedicationObserver::class); // เพิ่มบรรทัดนี้
        PrescribedMedication::observe(PrescribedMedicationObserver::class);
        InvoiceItem::observe(InvoiceItemObserver::class); // เพิ่มบรรทัดนี้

    }
}
