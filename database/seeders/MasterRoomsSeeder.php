<?php

namespace Database\Seeders;

use App\Models\Module\MasterData\MeetingRooms;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterRoomsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataRoom = array([
            'kd_room' => 'RM-001',
            'name' => 'Opal',
        ],[
            'kd_room' => 'RM-002',
            'name' => 'Ruby',
        ],[
            'kd_room' => 'RM-003',
            'name' => 'Saphire',
        ],[
            'kd_room' => 'RM-004',
            'name' => 'Emerald I'
        ],[
            'kd_room' => 'RM-005',
            'name' => 'Emerald II'
        ],[
            'kd_room' => 'RM-006',
            'name' => 'Emerald I-II'
        ],[
            'kd_room' => 'RM-007',
            'name' => 'Oria Room I'
        ],[
            'kd_room' => 'RM-008',
            'name' => 'Oria Room II'
        ],[
            'kd_room' => 'RM-009',
            'name' => 'Oria Ballroom'
        ]);

        foreach ($dataRoom as $val) {
            MeetingRooms::create($val);
        }
    }
}
