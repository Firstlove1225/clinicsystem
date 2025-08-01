<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Queue; // ต้อง import Model Queue
use App\Models\Patient; // ต้อง import Model Patient
use App\Models\Doctor; // ต้อง import Model Doctor

class QueueDisplay extends Component
{
    public $queues;

    // กำหนดให้ Livewire Component รีเฟรชข้อมูลทุกๆ 5 วินาที
    protected $listeners = ['echo:queue-updates,QueueUpdated' => 'refreshQueues'];
    protected $pollingInterval = 5000; // 5 วินาที

    public function mount()
    {
        $this->loadQueues();
    }

    public function refreshQueues()
    {
        $this->loadQueues();
    }

    private function loadQueues()
    {
        // ดึงคิวของวันนี้ที่มีสถานะ waiting, calling, in_progress
        // เรียงตามเวลาลงทะเบียน หรือตามหมายเลขคิว
        $this->queues = Queue::with(['patient', 'doctor'])
            ->whereDate('check_in_time', today()) // คิวของวันนี้เท่านั้น
            ->whereIn('status', ['waiting', 'calling', 'in_progress']) // สถานะที่ต้องการแสดง
            ->orderBy('check_in_time', 'asc')
            ->get();
    }

    public function render()
    {
        return view('livewire.queue-display');
    }
}
