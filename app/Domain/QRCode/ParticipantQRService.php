<?php

namespace App\Domain\QRCode;

use App\Domain\Participant\Participant;
use App\Enums\MeetingStatus;
use App\Enums\ParticipantStatus;
use App\Enums\QRCredentialStatus;
use App\Support\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;

class ParticipantQRService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function generate(Participant $participant, ?int $actorId = null, ?\DateTimeInterface $expiresAt = null): array
    {
        return DB::transaction(function () use ($participant, $actorId, $expiresAt) {
            ParticipantQRCredential::where('participant_id', $participant->id)
                ->where('status', QRCredentialStatus::ACTIVE->value)
                ->update(['status' => QRCredentialStatus::REVOKED->value, 'revoked_at' => now(), 'revoked_by' => $actorId]);

            $token = $this->newToken();
            $credential = ParticipantQRCredential::create([
                'participant_id' => $participant->id,
                'token_hash' => $this->hash($token),
                'token_last_four' => substr($token, -4),
                'status' => QRCredentialStatus::ACTIVE,
                'issued_at' => now(),
                'expires_at' => $expiresAt ?? $participant->meetingEvent?->end_at?->copy()->addDay(),
            ]);

            $this->auditLogger->record('participant_qr.generated', $participant->hotel_id, $actorId, $credential, ['participant_id' => $participant->id, 'last_four' => substr($token, -4)]);

            return ['credential' => $credential, 'token' => $token, 'url' => $this->url($token)];
        });
    }

    public function revoke(ParticipantQRCredential $credential, ?int $actorId = null): void
    {
        $credential->update(['status' => QRCredentialStatus::REVOKED, 'revoked_at' => now(), 'revoked_by' => $actorId]);
        $this->auditLogger->record('participant_qr.revoked', $credential->participant?->hotel_id, $actorId, $credential, ['last_four' => $credential->token_last_four]);
    }

    public function validate(string $token): array
    {
        $credential = ParticipantQRCredential::with('participant.meetingEvent')
            ->where('token_hash', $this->hash($token))
            ->first();

        if (! $credential) {
            return [false, null, 'INVALID_QR'];
        }
        if ($credential->status !== QRCredentialStatus::ACTIVE) {
            return [false, $credential, 'QR_REVOKED'];
        }
        if ($credential->expires_at && now()->greaterThan($credential->expires_at)) {
            return [false, $credential, 'QR_EXPIRED'];
        }

        $participant = $credential->participant;
        if (in_array($participant->status, [ParticipantStatus::BLOCKED, ParticipantStatus::CANCELLED], true)) {
            return [false, $credential, 'PARTICIPANT_BLOCKED'];
        }
        if ($participant->meetingEvent?->status === MeetingStatus::CANCELLED) {
            return [false, $credential, 'MEETING_CANCELLED'];
        }
        if ($participant->meetingEvent?->status === MeetingStatus::COMPLETED) {
            return [false, $credential, 'MEETING_COMPLETED'];
        }

        return [true, $credential, null];
    }

    public function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    public function url(string $token): string
    {
        return url('/scan/participant/'.$token);
    }

    private function newToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
