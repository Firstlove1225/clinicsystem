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
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('ชื่อยา');
            $table->string('generic_name')->nullable()->comment('ชื่อสามัญทางยา');
            $table->string('dosage_form')->nullable()->comment('รูปแบบยา (เช่น เม็ด, น้ำ, แคปซูล)');
            $table->string('strength')->nullable()->comment('ความแรง (เช่น 500 mg, 10 ml)');
            $table->text('description')->nullable()->comment('รายละเอียด/สรรพคุณ');
            $table->decimal('price_per_unit', 8, 2)->nullable()->comment('ราคาต่อหน่วย (เช่น ต่อเม็ด, ต่อขวด)');
            $table->string('unit')->nullable()->comment('หน่วย (เช่น เม็ด, ขวด, ซอง)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};
