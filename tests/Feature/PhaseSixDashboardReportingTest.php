<?php

namespace Tests\Feature;

use App\Domain\Booking\Booking;
use App\Domain\Booking\Client;
use App\Domain\Catering\MealSession;
use App\Domain\Hotel\Hotel;
use App\Domain\Meeting\MeetingEvent;
use App\Domain\Meeting\MeetingRoom;
use App\Domain\Participant\Participant;
use App\Domain\Redemption\ParticipantEntitlement;
use App\Domain\Redemption\Redemption;
use App\Domain\Reporting\ReportExport;
use App\Enums\MealSessionStatus;
use App\Enums\RedemptionStatus;
use App\Enums\ReportExportStatus;
use App\Enums\RoomOperationalStatus;
use App\Models\User;
use App\Support\Reporting\SpreadsheetSafe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PhaseSixDashboardReportingTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_uses_real_tenant_scoped_data(): void
    {
        [$hotel, $user, $meeting] = $this->reportFixture('P6A', 'Oria Phase Six Meeting');
        [$otherHotel] = $this->reportFixture('P6B', 'Foreign Phase Six Meeting');

        $this->actingAs($user)->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('dashboard.index', ['date' => $meeting->start_at->toDateString()]))
            ->assertOk()
            ->assertSee('Operational Dashboard')
            ->assertSee('Oria Phase Six Meeting')
            ->assertDontSee('Foreign Phase Six Meeting');

        $this->assertNotSame($hotel->id, $otherHotel->id);
    }

    public function test_report_view_and_export_permissions_are_enforced(): void
    {
        [$hotel, $user] = $this->reportFixture('P6C', 'Phase Six Meeting', []);
        $viewer = $this->user($hotel, ['report.view']);

        $this->actingAs($user)->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('reports.show', 'meetings'))
            ->assertForbidden();

        $this->actingAs($viewer)->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('reports.show', 'meetings'))
            ->assertOk();

        $this->actingAs($viewer)->post(route('reports.export', 'meetings'), ['format' => 'csv'])
            ->assertForbidden();
    }

    public function test_meeting_report_is_tenant_scoped_and_csv_export_works(): void
    {
        [$hotel, $user, $meeting] = $this->reportFixture('P6D', 'Scoped Report Meeting', ['report.view', 'report.export']);
        $this->reportFixture('P6E', 'Hidden Report Meeting');

        $this->actingAs($user)->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('reports.show', ['report' => 'meetings', 'date_from' => $meeting->start_at->toDateString(), 'date_to' => $meeting->start_at->toDateString()]))
            ->assertOk()
            ->assertSee('Scoped Report Meeting')
            ->assertDontSee('Hidden Report Meeting');

        $this->actingAs($user)->post(route('reports.export', 'meetings'), [
            'format' => 'csv',
            'date_from' => $meeting->start_at->toDateString(),
            'date_to' => $meeting->start_at->toDateString(),
        ])->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->assertSame($hotel->id, $user->hotel_id);
    }

    public function test_queued_export_download_and_cleanup_are_secure(): void
    {
        Storage::fake('local');
        [$hotel, $user] = $this->reportFixture('P6F', 'Queued Export Meeting', ['report.view', 'report.export']);
        $export = ReportExport::create([
            'hotel_id' => $hotel->id,
            'requested_by' => $user->id,
            'report_type' => 'meetings',
            'format' => 'csv',
            'filters' => [],
            'status' => ReportExportStatus::COMPLETED,
            'progress' => 100,
            'file_disk' => 'local',
            'file_path' => 'reports/exports/test/report.csv',
            'file_name' => 'report.csv',
            'row_count' => 1,
            'expires_at' => now()->addDay(),
        ]);
        Storage::disk('local')->put($export->file_path, 'Booking number,Meeting name');

        $this->actingAs($user)->get(route('reports.exports.download', $export))->assertOk();

        $export->update(['expires_at' => now()->subMinute()]);
        $this->artisan('reports:cleanup-expired')->assertSuccessful();
        $this->assertSame(ReportExportStatus::EXPIRED, $export->refresh()->status);
        Storage::disk('local')->assertMissing($export->file_path);
    }

    public function test_spreadsheet_formula_values_are_escaped(): void
    {
        $this->assertSame("'=SUM(A1:A2)", SpreadsheetSafe::value('=SUM(A1:A2)'));
        $this->assertSame('Ordinary value', SpreadsheetSafe::value('Ordinary value'));
    }

    private function reportFixture(string $hotelCode = 'ORIA', string $meetingName = 'Phase Six Meeting', array $permissions = ['report.view']): array
    {
        $hotel = Hotel::create(['code' => $hotelCode, 'name' => $hotelCode.' Hotel', 'timezone' => 'Asia/Jakarta']);
        $room = MeetingRoom::create(['hotel_id' => $hotel->id, 'code' => $hotelCode.'-R1', 'name' => $hotelCode.' Ballroom', 'capacity' => 20, 'operational_status' => RoomOperationalStatus::RESERVED]);
        $client = Client::create(['hotel_id' => $hotel->id, 'company_name' => $hotelCode.' Client']);
        $client->hotels()->syncWithoutDetaching([$hotel->id => ['status' => 'ACTIVE']]);
        $booking = Booking::create(['hotel_id' => $hotel->id, 'client_id' => $client->id, 'booking_number' => $hotelCode.'-B1', 'booking_date' => '2026-06-21', 'status' => 'CONFIRMED']);
        $meeting = MeetingEvent::create([
            'hotel_id' => $hotel->id,
            'booking_id' => $booking->id,
            'meeting_room_id' => $room->id,
            'event_name' => $meetingName,
            'event_date' => '2026-06-21',
            'start_at' => '2026-06-21 09:00:00+07',
            'end_at' => '2026-06-21 11:00:00+07',
            'expected_participants' => 2,
            'status' => 'SCHEDULED',
        ]);
        $participant = Participant::create(['hotel_id' => $hotel->id, 'meeting_event_id' => $meeting->id, 'participant_number' => $hotelCode.'-P1', 'full_name' => 'Phase Six Guest', 'status' => 'CHECKED_IN', 'registered_at' => now(), 'checked_in_at' => now()]);
        ParticipantEntitlement::create(['participant_id' => $participant->id, 'meeting_event_id' => $meeting->id, 'entitlement_type' => 'COFFEE_BREAK', 'total_quantity' => 1, 'redeemed_quantity' => 1, 'remaining_quantity' => 0]);
        $session = MealSession::create(['hotel_id' => $hotel->id, 'meeting_event_id' => $meeting->id, 'entitlement_type' => 'COFFEE_BREAK', 'session_number' => 1, 'name' => 'Coffee Break', 'status' => MealSessionStatus::OPEN]);
        Redemption::create(['hotel_id' => $hotel->id, 'participant_id' => $participant->id, 'meeting_event_id' => $meeting->id, 'meal_session_id' => $session->id, 'redemption_number' => $hotelCode.'-RDM1', 'redeemed_at' => now(), 'status' => RedemptionStatus::SUCCESS]);

        return [$hotel, $this->user($hotel, $permissions), $meeting];
    }

    private function user(Hotel $hotel, array $permissions): User
    {
        $user = User::create([
            'hotel_id' => $hotel->id,
            'name' => 'Phase Six User '.uniqid(),
            'username' => 'phase6'.uniqid(),
            'email' => uniqid().'@phase6.test',
            'password' => 'password',
        ]);

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $user->givePermissionTo($permissions);

        return $user;
    }
}
