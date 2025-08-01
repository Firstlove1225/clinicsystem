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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null')->comment('เชื่อมโยงกับผู้ใช้งานระบบ (ถ้ามี)');
            $table->string('first_name')->comment('ชื่อ');
            $table->string('last_name')->comment('นามสกุล');
            $table->string('specialty')->nullable()->comment('ความเชี่ยวชาญ');
            $table->string('license_number')->unique()->nullable()->comment('เลขที่ใบอนุญาตประกอบวิชาชีพ');
            $table->string('phone_number')->nullable()->comment('เบอร์โทรศัพท์');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
