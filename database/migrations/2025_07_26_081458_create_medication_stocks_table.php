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
        Schema::create('medication_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_id')->constrained()->onDelete('cascade'); // เชื่อมโยงกับข้อมูลยา
            $table->string('lot_number')->nullable()->comment('เลขที่ Lot');
            $table->integer('quantity')->comment('จำนวน');
            $table->date('expiry_date')->nullable()->comment('วันหมดอายุ');
            $table->string('location')->nullable()->comment('ตำแหน่งจัดเก็บ'); // เช่น ชั้น A, ตู้เย็น
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_stocks');
    }
};
