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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade'); // เชื่อมโยงกับใบแจ้งหนี้หลัก
            $table->string('item_type')->comment('ประเภทรายการ (service, medication)'); // ระบุว่าเป็นบริการหรือยา
            $table->unsignedBigInteger('item_id')->comment('ID ของบริการหรือยา'); // ID ของ Service หรือ Medication
            $table->string('item_name')->comment('ชื่อรายการ'); // ชื่อของรายการ (เพื่อความสะดวกในการแสดงผล)
            $table->integer('quantity')->default(1)->comment('จำนวน');
            $table->decimal('unit_price', 10, 2)->comment('ราคาต่อหน่วย');
            $table->decimal('sub_total', 10, 2)->comment('ราคารวมของรายการนี้ (quantity * unit_price)');
            $table->timestamps();

            // เพิ่ม index เพื่อประสิทธิภาพในการ query
            $table->index(['item_type', 'item_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
