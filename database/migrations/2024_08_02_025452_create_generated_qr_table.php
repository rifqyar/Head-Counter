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
        Schema::create('qr_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id');
            $table->string('qr_path')->nullable(false);
            $table->dateTimeTz('qr_valid')->nullable(false);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_detail');
    }
};
