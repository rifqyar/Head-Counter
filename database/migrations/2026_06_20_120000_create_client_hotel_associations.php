<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_hotel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('hotel_id')->constrained('hotels')->restrictOnDelete();
            $table->string('hotel_specific_code')->nullable();
            $table->string('status', 20)->default('ACTIVE');
            $table->text('notes')->nullable();
            $table->jsonb('metadata')->default('{}');
            $table->timestampsTz();
            $table->unique(['client_id', 'hotel_id']);
            $table->index(['hotel_id', 'status']);
        });

        DB::statement("
            INSERT INTO client_hotel (client_id, hotel_id, hotel_specific_code, status, metadata, created_at, updated_at)
            SELECT id, hotel_id, external_id, 'ACTIVE', jsonb_build_object('source', 'clients.hotel_id_backfill'), now(), now()
            FROM clients
            WHERE hotel_id IS NOT NULL
            ON CONFLICT (client_id, hotel_id) DO NOTHING
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('client_hotel');
    }
};
