<?php

namespace App\Providers;

use App\Domain\Attendance\MeetingAttendance;
use App\Domain\Audit\AuditLog;
use App\Domain\Booking\Booking;
use App\Domain\Booking\Client;
use App\Domain\Catering\MealSession;
use App\Domain\Catering\MeetingPackage;
use App\Domain\Hotel\Hotel;
use App\Domain\Integration\IntegrationApiKey;
use App\Domain\Meeting\MeetingEvent;
use App\Domain\Meeting\MeetingRoom;
use App\Domain\Participant\Participant;
use App\Domain\Redemption\Redemption;
use App\Models\User;
use App\Policies\AuditLogPolicy;
use App\Policies\BookingPolicy;
use App\Policies\ClientPolicy;
use App\Policies\HotelPolicy;
use App\Policies\IntegrationApiKeyPolicy;
use App\Policies\MealSessionPolicy;
use App\Policies\MeetingAttendancePolicy;
use App\Policies\MeetingEventPolicy;
use App\Policies\MeetingPackagePolicy;
use App\Policies\MeetingRoomPolicy;
use App\Policies\ParticipantPolicy;
use App\Policies\RedemptionPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Hotel::class => HotelPolicy::class,
        MeetingRoom::class => MeetingRoomPolicy::class,
        Client::class => ClientPolicy::class,
        Booking::class => BookingPolicy::class,
        MeetingEvent::class => MeetingEventPolicy::class,
        MeetingPackage::class => MeetingPackagePolicy::class,
        Participant::class => ParticipantPolicy::class,
        MeetingAttendance::class => MeetingAttendancePolicy::class,
        MealSession::class => MealSessionPolicy::class,
        Redemption::class => RedemptionPolicy::class,
        AuditLog::class => AuditLogPolicy::class,
        User::class => UserPolicy::class,
        IntegrationApiKey::class => IntegrationApiKeyPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
