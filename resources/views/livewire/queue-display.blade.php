<div class="p-6">
    <h1 class="text-3xl font-bold mb-6 text-center text-primary-600">สถานะคิวคลินิกวันนี้</h1>

    @if ($queues->isEmpty())
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative text-center"
            role="alert">
            <strong class="font-bold">ไม่มีคิวในขณะนี้!</strong>
            <span class="block sm:inline">โปรดรอคนไข้รายต่อไป</span>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($queues as $queue)
                <div @class([
                    'bg-white rounded-lg shadow-lg p-6',
                    'border-l-4 border-yellow-500' => $queue->status === 'waiting',
                    'border-l-4 border-orange-500' => $queue->status === 'calling',
                    'border-l-4 border-blue-500' => $queue->status === 'in_progress',
                ])>
                    <div class="flex items-center justify-between mb-4">
                        <span
                            class="text-4xl font-extrabold {{ ($queue->status === 'waiting' ? 'text-yellow-700' : '') .
                                ($queue->status === 'calling' ? 'text-orange-700' : '') .
                                ($queue->status === 'in_progress' ? 'text-blue-700' : '') }}">{{ $queue->queue_number }}</span>
                        <span @class([
                            'px-3 py-1 text-sm font-semibold rounded-full',
                            'bg-yellow-200 text-yellow-800' => $queue->status === 'waiting',
                            'bg-orange-200 text-orange-800' => $queue->status === 'calling',
                            'bg-blue-200 text-blue-800' => $queue->status === 'in_progress',
                        ])>
                            @if ($queue->status === 'waiting')
                                รอเรียก
                            @elseif ($queue->status === 'calling')
                                กำลังเรียก
                            @elseif ($queue->status === 'in_progress')
                                กำลังตรวจ
                            @endif
                        </span>
                    </div>
                    <h2 class="text-xl font-semibold mb-2 text-gray-800">
                        {{ $queue->patient->first_name }} {{ $queue->patient->last_name }} (HN:
                        {{ $queue->patient->hn }})
                    </h2>
                    <p class="text-gray-600 mb-2">แพทย์:
                        {{ $queue->doctor ? "{$queue->doctor->first_name} {$queue->doctor->last_name}" : '-' }}</p>
                    <p class="text-gray-500 text-sm">ลงทะเบียน: {{ $queue->check_in_time->format('H:i') }} น.</p>
                </div>
            @endforeach
        </div>
    @endif
</div>
