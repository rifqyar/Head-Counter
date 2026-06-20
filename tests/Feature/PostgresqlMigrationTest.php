<?php

namespace Tests\Feature;

use App\Enums\RoomStatusEnum;
use App\Models\Module\MasterData\Client;
use App\Models\Module\MasterData\MeetingRooms;
use App\Models\Module\MasterData\MeetingSchedule;
use App\Models\Module\MasterData\Package;
use App\Models\Module\Setting\RoomStatus;
use App\Models\Transaction\QRDetail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostgresqlMigrationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_package_price_is_stored_as_decimal(): void
    {
        $package = Package::create([
            'kd_pck' => 'PG-PRICE',
            'name' => 'PostgreSQL Price Package',
            'price' => 123456.78,
            'details' => 'Price test',
            'count_qr' => 1,
        ]);

        $this->assertSame('123456.78', $package->refresh()->price);
    }

    public function test_canonical_room_status_relationships_work(): void
    {
        RoomStatus::firstOrCreate(
            ['kd_status' => RoomStatusEnum::Available],
            [
                'name' => 'Available',
                'description' => 'Available',
            ]
        );

        $room = MeetingRooms::create([
            'kd_room' => 'PG-ROOM',
            'name' => 'PostgreSQL Room',
            'room_availability' => RoomStatusEnum::Available,
        ]);

        $this->assertSame(RoomStatusEnum::Available, $room->status->kd_status);
    }

    public function test_meeting_qr_foreign_key_relationship_uses_legacy_columns(): void
    {
        Client::create([
            'code' => 'PGC',
            'name' => 'PostgreSQL Client',
            'contact_person' => 'Contact',
            'company_phone' => '08123456789',
            'email' => 'pg-client@example.test',
        ]);

        RoomStatus::firstOrCreate(
            ['kd_status' => RoomStatusEnum::Available],
            [
                'name' => 'Available',
                'description' => 'Available',
            ]
        );

        MeetingRooms::create([
            'kd_room' => 'PG-ROOM',
            'name' => 'PostgreSQL Room',
            'room_availability' => RoomStatusEnum::Available,
        ]);

        Package::create([
            'kd_pck' => 'PG-PACK',
            'name' => 'PostgreSQL Package',
            'price' => 150000,
            'details' => 'Package test',
            'count_qr' => 1,
        ]);

        $schedule = MeetingSchedule::create([
            'trx_number' => 'TRX/MT-SCHD/PGC/2026/0001',
            'code_client' => 'PGC',
            'tgl_start' => now()->toDateString(),
            'tgl_end' => now()->toDateString(),
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '10:00:00',
            'kuota' => 5,
            'qr_path' => '0',
            'package' => 'PG-PACK',
            'room' => 'PG-ROOM',
        ]);

        $qr = QRDetail::create([
            'meeting_id' => $schedule->id,
            'qr_path' => 'pg-token.png',
            'qr_valid_start' => now(),
            'qr_valid_end' => now()->addHour(),
        ]);

        $this->assertSame($schedule->id, $qr->refresh()->meeting_id);
    }

    public function test_postgresql_driver_is_used_for_tests(): void
    {
        $this->assertSame('pgsql', DB::connection()->getDriverName());
    }
}
