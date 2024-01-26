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
        Schema::create('m_client', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3);
            $table->string('name', 250);
            $table->string('contact_person', 250);
            $table->string('company_phone', 18);
            $table->string('email', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_client');
    }
};
