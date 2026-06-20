<?php

namespace Tests\Feature;

use App\Domain\Hotel\Hotel;
use App\Models\Module\MasterData\Client;
use App\Models\Module\MasterData\MeetingRooms;
use App\Models\Module\MasterData\MeetingSchedule;
use App\Models\Module\MasterData\Package;
use App\Models\Module\Setting\RoomStatus;
use App\Models\Transaction\QRDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PhaseOneSmokeTest extends TestCase
{
    use DatabaseTransactions;

    private function userWithPermissions(array $permissions): User
    {
        $hotel = Hotel::firstOrCreate(
            ['code' => 'P1TEST'],
            ['name' => 'Phase One Test Hotel']
        );

        $user = User::create([
            'name' => 'Phase One Tester',
            'username' => 'phase_one_'.uniqid(),
            'hotel_id' => $hotel->id,
            'password' => Hash::make('password'),
        ]);

        $role = Role::firstOrCreate(['name' => 'Phase One Tester', 'guard_name' => 'web']);

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_user_can_log_in_with_valid_credentials(): void
    {
        $user = User::create([
            'name' => 'Login Tester',
            'username' => 'login_'.uniqid(),
            'password' => Hash::make('password'),
        ]);

        $this->post('/login', [
            'username' => $user->username,
            'password' => 'password',
        ])->assertRedirect('/home');
    }

    public function test_authenticated_user_can_access_client_list(): void
    {
        $user = $this->userWithPermissions(['Client']);

        $this->actingAs($user)
            ->get(route('masterdata.client'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();
    }

    public function test_authenticated_user_can_access_meeting_schedule_list(): void
    {
        $user = $this->userWithPermissions(['Meeting Schedule']);

        $this->actingAs($user)
            ->get(route('masterdata.meeting-schedule'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();
    }

    public function test_attendance_form_access_for_valid_qr(): void
    {
        Client::create([
            'code' => 'TST',
            'name' => 'Test Client',
            'contact_person' => 'Tester',
            'company_phone' => '08123456789',
            'email' => 'client@example.test',
        ]);

        RoomStatus::firstOrCreate(
            ['kd_status' => 'AVAILABLE'],
            [
                'name' => 'Available',
                'description' => 'Available',
            ]
        );

        MeetingRooms::create([
            'kd_room' => 'ROOM',
            'name' => 'Test Room',
            'room_availability' => 'AVAILABLE',
        ]);

        Package::create([
            'kd_pck' => 'PKG',
            'name' => 'Test Package',
            'price' => 100000,
            'details' => 'Test package',
            'count_qr' => 1,
        ]);

        $schedule = MeetingSchedule::create([
            'trx_number' => 'TRX/MT-SCHD/TST/2026/0001',
            'code_client' => 'TST',
            'tgl_start' => now()->toDateString(),
            'tgl_end' => now()->toDateString(),
            'jam_mulai' => now()->subHour()->format('H:i:s'),
            'jam_selesai' => now()->addHour()->format('H:i:s'),
            'kuota' => 10,
            'qr_path' => '0',
            'package' => 'PKG',
            'room' => 'ROOM',
        ]);

        $qr = QRDetail::create([
            'meeting_id' => $schedule->id,
            'qr_path' => 'phase-one-valid-token.png',
            'qr_valid_start' => now()->subHour(),
            'qr_valid_end' => now()->addHour(),
        ]);

        $this->get(route('meeting-attendance.form-attendance', [
            'meeting_id' => base64_encode($schedule->trx_number),
            'qr_code' => $qr->id,
            'qr_token' => 'phase-one-valid-token',
        ]))->assertOk()
            ->assertSee('Form Meeting Attendance');
    }

    public function test_invalid_qr_does_not_return_server_error(): void
    {
        $this->get(route('meeting-attendance.form-attendance', [
            'meeting_id' => base64_encode('missing'),
            'qr_code' => 999999,
        ]))->assertOk()
            ->assertSee('QR Code ini sudah kadaluarsa');
    }

    public function test_master_data_routes_require_authentication(): void
    {
        $this->get(route('masterdata.client'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertRedirect('/login');
    }

    public function test_permission_middleware_blocks_users_without_permission(): void
    {
        $user = User::create([
            'name' => 'No Permission Tester',
            'username' => 'no_permission_'.uniqid(),
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user)
            ->get(route('setting.permission'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertForbidden();
    }
}
