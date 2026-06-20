<?php

namespace App\Domain\QRCode;

use App\Domain\Meeting\MeetingEvent;
use App\Enums\MeetingStatus;
use App\Support\Audit\AuditLogger;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MeetingQRService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function generate(MeetingEvent $meeting, ?int $actorId = null, ?\DateTimeInterface $expiresAt = null): array
    {
        $token = $this->newToken();
        $path = $this->writeQr($meeting, $token);
        $meeting->forceFill([
            'meeting_qr_token_hash' => $this->hash($token),
            'meeting_qr_token_last_four' => substr($token, -4),
            'meeting_qr_issued_at' => now(),
            'meeting_qr_expires_at' => $expiresAt ?? $meeting->end_at?->copy()->addDay(),
            'meeting_qr_revoked_at' => null,
            'meeting_qr_path' => $path,
        ])->save();

        $this->auditLogger->record('meeting_qr.generated', $meeting->hotel_id, $actorId, $meeting, ['last_four' => substr($token, -4), 'path' => $path]);

        return ['token' => $token, 'url' => $this->url($token), 'path' => $path];
    }

    public function regenerate(MeetingEvent $meeting, ?int $actorId = null): array
    {
        $result = $this->generate($meeting, $actorId);
        $this->auditLogger->record('meeting_qr.regenerated', $meeting->hotel_id, $actorId, $meeting, ['last_four' => $meeting->meeting_qr_token_last_four]);

        return $result;
    }

    public function revoke(MeetingEvent $meeting, ?int $actorId = null): void
    {
        $meeting->forceFill(['meeting_qr_revoked_at' => now()])->save();
        $this->auditLogger->record('meeting_qr.revoked', $meeting->hotel_id, $actorId, $meeting, ['last_four' => $meeting->meeting_qr_token_last_four]);
    }

    public function resolve(string $token): ?MeetingEvent
    {
        return MeetingEvent::withoutGlobalScope('hotel')->where('meeting_qr_token_hash', $this->hash($token))->first();
    }

    public function validate(string $token): array
    {
        $meeting = $this->resolve($token);

        if (! $meeting || ! $meeting->meeting_qr_token_hash) {
            return [false, null, 'Invalid meeting QR.'];
        }
        if ($meeting->meeting_qr_revoked_at) {
            return [false, $meeting, 'Meeting QR has been revoked.'];
        }
        if ($meeting->meeting_qr_expires_at && now()->greaterThan($meeting->meeting_qr_expires_at)) {
            return [false, $meeting, 'Meeting QR has expired.'];
        }
        if ($meeting->checkin_open_at && now()->lessThan($meeting->checkin_open_at)) {
            return [false, $meeting, 'Registration is not open yet.'];
        }
        if ($meeting->checkin_close_at && now()->greaterThan($meeting->checkin_close_at)) {
            return [false, $meeting, 'Registration is closed.'];
        }
        if (in_array($meeting->status, [MeetingStatus::CANCELLED, MeetingStatus::COMPLETED, MeetingStatus::NO_SHOW], true)) {
            return [false, $meeting, 'Meeting is not accepting registration.'];
        }

        return [true, $meeting, null];
    }

    public function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    public function url(string $token): string
    {
        return url('/attendance/meeting/'.$token);
    }

    private function newToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    private function writeQr(MeetingEvent $meeting, string $token): string
    {
        $path = 'qrcodes/meeting-'.$meeting->id.'-'.Str::uuid().'.svg';
        Storage::disk('public')->put($path, QrCode::format('svg')->size(300)->generate($this->url($token)));

        return $path;
    }
}
