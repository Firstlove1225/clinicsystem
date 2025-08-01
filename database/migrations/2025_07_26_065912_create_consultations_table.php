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
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade')->comment('รหัสคนไข้');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade')->comment('รหัสแพทย์ผู้ทำการตรวจ');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null')->comment('เชื่อมโยงกับการนัดหมาย (ถ้ามี)');
            $table->foreignId('queue_id')->nullable()->constrained()->onDelete('set null')->comment('เชื่อมโยงกับคิว (ถ้ามี)');
            $table->timestamp('consultation_date')->useCurrent()->comment('วันที่และเวลาที่ตรวจ');
            $table->text('chief_complaint')->nullable()->comment('อาการสำคัญ');
            $table->text('diagnosis')->nullable()->comment('การวินิจฉัย');
            $table->text('treatment')->nullable()->comment('การรักษา');
            $table->text('medication')->nullable()->comment('ยาที่สั่ง');
            $table->longText('notes')->nullable()->comment('บันทึกเพิ่มเติม');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
