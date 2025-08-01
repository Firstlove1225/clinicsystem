<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade')->comment('รหัสคนไข้');
            $table->foreignId('doctor_id')->nullable()->constrained()->onDelete('set null')->comment('รหัสแพทย์ที่ถูกส่งไป');
            $table->string('queue_number')->comment('หมายเลขคิว'); // อาจจะเป็น A001, B002 หรือ 1, 2, 3
            $table->timestamp('check_in_time')->useCurrent()->comment('เวลาลงทะเบียนเข้าตรวจ');
            $table->string('status')->default('waiting')->comment('สถานะคิว (waiting, calling, in_progress, completed, cancelled)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
