<?php

namespace Database\Seeders;

use App\Models\Module\Setting\RoomStatus;
use Illuminate\Database\Seeder;

class RoomStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataRoom = [[
            'kd_status' => 'AVAILABLE',
            'name' => 'Available',
            'description' => 'Ruangan Tersedia',
        ], [
            'kd_status' => 'RESERVED',
            'name' => 'Reserved',
            'description' => 'Ruangan sudah dibooking untuk acara lain',
        ], [
            'kd_status' => 'OCCUPIED',
            'name' => 'Occupied',
            'description' => 'Ruangan sedang digunakan untuk acara lain',
        ], [
            'kd_status' => 'CLEANING',
            'name' => 'Cleaning',
            'description' => 'Ruangan sedang dibersihkan',
        ], [
            'kd_status' => 'MAINTENANCE',
            'name' => 'Maintenance',
            'description' => 'Ruangan sedang dalam perawatan',
        ], [
            'kd_status' => 'INACTIVE',
            'name' => 'Inactive',
            'description' => 'Ruangan tidak aktif',
        ]];

        foreach ($dataRoom as $val) {
            RoomStatus::updateOrCreate(['kd_status' => $val['kd_status']], $val);
        }
    }
}
