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
        Schema::create('trx_meeting_attendance', function (Blueprint $table) {
            $table->id();
            $table->string('trx_metting_number');
            $table->string('name');
            $table->string('phone_number');
            $table->text('address');
            $table->string('qr_path');
            $table->integer('scanned_qr');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trx_meeting_attendance');
    }
};
