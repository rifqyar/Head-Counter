<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP VIEW IF EXISTS r_room_status CASCADE');
        }

        Schema::create('r_room_status', function (Blueprint $table) {
            $table->id();
            $table->string('kd_status')->unique();
            $table->string('name');
            $table->string('description');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('r_room_status');
    }
};
