<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class AppointmentCalendarPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days'; // ไอคอนสำหรับเมนู
    protected static ?string $title = 'ปฏิทินนัดหมาย'; // ชื่อที่แสดงบนหัวหน้า
    protected static ?string $navigationLabel = 'ปฏิทินนัดหมาย'; // ชื่อที่แสดงในเมนูนำทาง
    protected static ?string $slug = 'appointment-calendar'; // URL ของหน้านี้ (เช่น /admin/appointment-calendar)
    protected static ?string $navigationGroup = 'การจัดการนัดหมายและคิว'; // จัดกลุ่มเมนู

    protected static string $view = 'filament.pages.appointment-calendar-page';

    // หากต้องการให้หน้านี้เข้าถึงได้โดยไม่ต้องเข้าสู่ระบบ Filament
    // public static bool $shouldRegisterNavigation = false; // ถ้าเป็น false จะไม่แสดงในเมนูนำทาง
}
