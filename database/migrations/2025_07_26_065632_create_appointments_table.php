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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade')->comment('รหัสคนไข้');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade')->comment('รหัสแพทย์');
            $table->date('appointment_date')->comment('วันที่นัดหมาย');
            $table->time('appointment_time')->comment('เวลานัดหมาย');
            $table->text('reason')->nullable()->comment('เหตุผลการนัดหมาย');
            $table->string('status')->default('pending')->comment('สถานะการนัดหมาย (pending, confirmed, completed, cancelled)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
