<?php

namespace App\Actions;

use App\Domain\Attendance\MeetingAttendance;
use App\Domain\Meeting\MeetingEvent;
use App\Domain\Participant\Participant;
use App\Domain\QRCode\ParticipantQRService;
use App\Domain\Redemption\ParticipantEntitlementService;
use App\Enums\AttendanceType;
use App\Enums\MeetingStatus;
use App\Exceptions\DomainException;
use App\Support\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterParticipantAction
{
    public function __construct(
        private readonly ParticipantEntitlementService $entitlementService,
        private readonly ParticipantQRService $participantQRService,
        private readonly AuditLogger $auditLogger
    ) {}

    public function execute(MeetingEvent $meetingEvent, array $data): Participant
    {
        return $this->executeWithQr($meetingEvent, $data)['participant'];
    }

    public function executeWithQr(MeetingEvent $meetingEvent, array $data, ?int $actorId = null): array
    {
        $data['normalized_email'] = $this->normalizeEmail($data['email'] ?? null);
        $data['normalized_phone'] = $this->normalizePhone($data['phone'] ?? null);
        $data['identity_reference'] = $this->blankToNull($data['identity_reference'] ?? null);

        $this->assertMeetingAcceptsRegistration($meetingEvent);

        $duplicate = Participant::withoutGlobalScope('hotel')
            ->where('meeting_event_id', $meetingEvent->id)
            ->where(function ($query) use ($data) {
                $query
                    ->when($data['normalized_email'] ?? null, fn ($q, $value) => $q->orWhere('normalized_email', $value))
                    ->when($data['normalized_phone'] ?? null, fn ($q, $value) => $q->orWhere('normalized_phone', $value))
                    ->when($data['identity_reference'] ?? null, fn ($q, $value) => $q->orWhere('identity_reference', $value));
            })
            ->first();

        if ($duplicate) {
            throw new DomainException('Participant already exists for this meeting.');
        }

        return DB::transaction(function () use ($meetingEvent, $data, $actorId) {
            $participant = Participant::create(array_merge($data, [
                'hotel_id' => $meetingEvent->hotel_id,
                'meeting_event_id' => $meetingEvent->id,
                'participant_number' => $data['participant_number'] ?? $this->nextParticipantNumber($meetingEvent),
                'registered_at' => $data['registered_at'] ?? now(),
            ]));

            MeetingAttendance::firstOrCreate(
                ['participant_id' => $participant->id, 'attendance_type' => AttendanceType::MEETING_CHECKIN->value],
                [
                    'meeting_event_id' => $meetingEvent->id,
                    'attended_at' => now(),
                    'verification_method' => 'MEETING_QR',
                    'metadata' => ['source' => $data['registration_source'] ?? 'MEETING_QR'],
                ]
            );

            $entitlements = $this->entitlementService->generateForParticipant($participant, $actorId);
            $credential = $this->participantQRService->generate($participant->load('meetingEvent'), $actorId);
            $this->auditLogger->record('participant.registered', $meetingEvent->hotel_id, $actorId, $participant, ['source' => $data['registration_source'] ?? 'MEETING_QR']);

            return [
                'participant' => $participant,
                'entitlements' => $entitlements,
                'credential' => $credential['credential'],
                'participant_qr_token' => $credential['token'],
                'participant_qr_url' => $credential['url'],
            ];
        });
    }

    private function assertMeetingAcceptsRegistration(MeetingEvent $meetingEvent): void
    {
        if (in_array($meetingEvent->status, [MeetingStatus::CANCELLED, MeetingStatus::COMPLETED, MeetingStatus::NO_SHOW], true)) {
            throw new DomainException('Meeting is not accepting registration.');
        }

        if ($meetingEvent->expected_participants > 0) {
            $count = Participant::withoutGlobalScope('hotel')->where('meeting_event_id', $meetingEvent->id)->count();
            if ($count >= $meetingEvent->expected_participants) {
                throw new DomainException('Participant quota has been reached.');
            }
        }
    }

    private function normalizeEmail(?string $email): ?string
    {
        return $this->blankToNull($email) ? mb_strtolower(trim($email)) : null;
    }

    private function normalizePhone(?string $phone): ?string
    {
        $phone = $this->blankToNull($phone);

        return $phone ? preg_replace('/[^0-9+]/', '', $phone) : null;
    }

    private function blankToNull(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return $value === '' ? null : $value;
    }

    private function nextParticipantNumber(MeetingEvent $meetingEvent): string
    {
        $next = Participant::withoutGlobalScope('hotel')->where('meeting_event_id', $meetingEvent->id)->count() + 1;

        return $meetingEvent->legacy_trx_number
            ? $meetingEvent->legacy_trx_number.'-'.Str::padLeft((string) $next, 4, '0')
            : 'P-'.Str::padLeft((string) $meetingEvent->id, 6, '0').'-'.Str::padLeft((string) $next, 4, '0');
    }
}
