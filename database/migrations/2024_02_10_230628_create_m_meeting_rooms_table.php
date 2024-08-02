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
        Schema::create('m_meeting_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('kd_room', 10);
            $table->string('name');
            $table->string('room_availability')->default('001');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_meeting_rooms');
    }
};
