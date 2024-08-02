<?php

namespace Database\Seeders;

use App\Models\Module\Setting\RoomStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataRoom = array([
            'kd_status' => '001',
            'name' => 'Available',
            'description' => 'Ruangan Tersedia',
        ],[
            'kd_status' => '002',
            'name' => 'Booked',
            'description' => 'Ruangan sudah dibooking untuk acara lain',
        ],[
            'kd_status' => '003',
            'name' => 'Occupied',
            'description' => 'Ruangan sedang digunakan untuk acara lain',
        ]);

        foreach ($dataRoom as $val) {
            RoomStatus::create($val);
        }
    }
}
