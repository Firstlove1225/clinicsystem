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
        Schema::create('prescribed_medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained()->onDelete('cascade'); // เชื่อมโยงกับการตรวจรักษา
            $table->foreignId('medication_id')->constrained()->onDelete('cascade'); // เชื่อมโยงกับข้อมูลยา
            $table->integer('quantity')->comment('จำนวนที่สั่งจ่าย');
            $table->string('unit')->nullable()->comment('หน่วยที่สั่งจ่าย (เช่น เม็ด, ขวด)');
            $table->string('instructions')->nullable()->comment('คำแนะนำการใช้ยา');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescribed_medications');
    }
};
