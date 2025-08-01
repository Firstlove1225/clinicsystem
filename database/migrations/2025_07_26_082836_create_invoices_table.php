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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique()->comment('เลขที่ใบแจ้งหนี้');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade'); // เชื่อมโยงกับคนไข้
            $table->foreignId('consultation_id')->nullable()->constrained()->onDelete('set null'); // เชื่อมโยงกับการตรวจรักษา (nullable)
            $table->decimal('total_amount', 10, 2)->default(0.00)->comment('ยอดรวม');
            $table->decimal('paid_amount', 10, 2)->default(0.00)->comment('ยอดที่ชำระแล้ว');
            $table->string('status')->default('pending')->comment('สถานะ (pending, paid, partial_paid, cancelled)');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
