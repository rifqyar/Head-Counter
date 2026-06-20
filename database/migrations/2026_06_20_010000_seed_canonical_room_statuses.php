<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();
        $statuses = [
            ['kd_status' => 'AVAILABLE', 'name' => 'Available', 'description' => 'Ruangan Tersedia'],
            ['kd_status' => 'RESERVED', 'name' => 'Reserved', 'description' => 'Ruangan sudah dibooking untuk acara lain'],
            ['kd_status' => 'OCCUPIED', 'name' => 'Occupied', 'description' => 'Ruangan sedang digunakan untuk acara lain'],
            ['kd_status' => 'CLEANING', 'name' => 'Cleaning', 'description' => 'Ruangan sedang dibersihkan'],
            ['kd_status' => 'MAINTENANCE', 'name' => 'Maintenance', 'description' => 'Ruangan sedang dalam perawatan'],
            ['kd_status' => 'INACTIVE', 'name' => 'Inactive', 'description' => 'Ruangan tidak aktif'],
        ];

        foreach ($statuses as $status) {
            DB::table('r_room_status')->updateOrInsert(
                ['kd_status' => $status['kd_status']],
                $status + ['created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep canonical reference data. Removing it could break room references.
    }
};
