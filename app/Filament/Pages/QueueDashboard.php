<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class QueueDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $title = 'หน้าจอคิว (Live)'; // ชื่อที่แสดงในเมนู
    protected static ?string $navigationLabel = 'หน้าจอคิว (Live)';
    protected static ?string $slug = 'queue-display'; // URL ของหน้านี้ (เช่น /admin/queue-display)
    protected static ?string $navigationGroup = 'การจัดการนัดหมายและคิว'; // จัดกลุ่มเมนู

    protected static string $view = 'filament.pages.queue-dashboard';

    // สามารถเพิ่ม logic เพิ่มเติมที่นี่ได้ เช่น กำหนดว่าใครเข้าถึงหน้านี้ได้
    protected static bool $shouldRegisterNavigation = true; // ให้แสดงในเมนูนำทาง
}
