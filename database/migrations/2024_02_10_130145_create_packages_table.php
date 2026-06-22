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
            DB::statement('DROP VIEW IF EXISTS m_packages CASCADE');
        }

        Schema::create('m_packages', function (Blueprint $table) {
            $table->id();
            $table->string('kd_pck', 10)->unique();
            $table->string('name');
            $table->decimal('price', 15, 2);
            $table->text('details');
            $table->integer('count_qr');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_packages');
    }
};
