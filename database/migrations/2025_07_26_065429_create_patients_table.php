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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('hn')->unique()->comment('เลขที่คนไข้'); // HN Number (Hospital Number)
            $table->string('first_name')->comment('ชื่อ');
            $table->string('last_name')->comment('นามสกุล');
            $table->date('date_of_birth')->comment('วันเดือนปีเกิด');
            $table->string('gender')->comment('เพศ'); // เช่น Male, Female, Other
            $table->string('address')->nullable()->comment('ที่อยู่');
            $table->string('phone_number')->nullable()->comment('เบอร์โทรศัพท์');
            $table->string('emergency_contact_name')->nullable()->comment('ชื่อผู้ติดต่อฉุกเฉิน');
            $table->string('emergency_contact_phone')->nullable()->comment('เบอร์โทรผู้ติดต่อฉุกเฉิน');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
