<div>
    <div id='calendar'></div>
</div>

@push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script>
        document.addEventListener('livewire:initialized', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth', // มุมมองเริ่มต้น (สามารถเปลี่ยนเป็น timeGridWeek, timeGridDay ได้)
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                locale: 'th', // ตั้งค่าภาษาไทย
                buttonText: {
                    today: 'วันนี้',
                    month: 'เดือน',
                    week: 'สัปดาห์',
                    day: 'วัน'
                },
                events: @json($events), // ดึงข้อมูล events จาก Livewire component
                eventClick: function(info) {
                    // เมื่อคลิกที่ event ในปฏิทิน ให้เปิดหน้าแก้ไขนัดหมายใน Filament
                    if (info.event.url) {
                        window.open(info.event.url, '_blank'); // เปิดในแท็บใหม่
                        info.jsEvent.preventDefault(); // ป้องกันการเปลี่ยนหน้า default
                    }
                },
                // เพิ่มเติม: สามารถเพิ่ม eventDidMount สำหรับปรับแต่งการแสดงผล event ได้
                eventDidMount: function(info) {
                    // ตัวอย่าง: เพิ่ม tooltip เมื่อ hover
                    // new Tooltip(info.el, {
                    //     title: info.event.extendedProps.reason || 'ไม่มีเหตุผล',
                    //     placement: 'top',
                    //     trigger: 'hover',
                    //     container: 'body'
                    // });
                }
            });
            calendar.render();

            // ฟังเหตุการณ์ Livewire เพื่อรีเฟรชปฏิทินหากข้อมูลมีการเปลี่ยนแปลง
            Livewire.on('eventsUpdated', () => {
                calendar.refetchEvents();
            });
        });
    </script>
@endpush
