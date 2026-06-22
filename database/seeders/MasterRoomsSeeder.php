<?php

namespace Database\Seeders;

use App\Models\Module\MasterData\MeetingRooms;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class MasterRoomsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataRoom = [[
            'kd_room' => 'RM-001',
            'name' => 'Opal',
            'capacity' => 20,
        ], [
            'kd_room' => 'RM-002',
            'name' => 'Ruby',
            'capacity' => 20,
        ], [
            'kd_room' => 'RM-003',
            'name' => 'Saphire',
            'capacity' => 20,
        ], [
            'kd_room' => 'RM-004',
            'name' => 'Emerald I',
            'capacity' => 20,
        ], [
            'kd_room' => 'RM-005',
            'name' => 'Emerald II',
            'capacity' => 20,
        ], [
            'kd_room' => 'RM-006',
            'name' => 'Emerald I-II',
            'capacity' => 20,
        ], [
            'kd_room' => 'RM-007',
            'name' => 'Oria Room I',
            'capacity' => 20,
        ], [
            'kd_room' => 'RM-008',
            'name' => 'Oria Room II',
            'capacity' => 20,
        ], [
            'kd_room' => 'RM-009',
            'name' => 'Oria Ballroom',
            'capacity' => 180,
        ]];

        $columns = Schema::getColumnListing('m_meeting_rooms');

        foreach ($dataRoom as $val) {
            MeetingRooms::updateOrCreate(
                ['kd_room' => $val['kd_room']],
                Arr::only($val, $columns)
            );
        }
    }
}
