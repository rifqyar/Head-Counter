<?php

namespace App\Actions;

use App\Domain\Catering\MealSession;
use App\Domain\Participant\Participant;
use App\Domain\QRCode\ParticipantQRService;
use App\Domain\Redemption\ParticipantEntitlement;
use App\Domain\Redemption\Redemption;
use App\Enums\MealSessionStatus;
use App\Enums\ParticipantStatus;
use App\Enums\RedemptionStatus;
use App\Enums\RejectionCode;
use App\Support\Audit\AuditLogger;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RedeemParticipantAction
{
    public const OVERRIDEABLE_REJECTION_CODES = [
        'SESSION_NOT_OPEN',
        'SESSION_EXPIRED',
        'NO_ENTITLEMENT',
        'ALREADY_REDEEMED',
        'QUOTA_EXHAUSTED',
        'MEETING_COMPLETED',
    ];

    public function __construct(
        private readonly ParticipantQRService $participantQRService,
        private readonly AuditLogger $auditLogger
    ) {}

    public function validateOnly(array $payload, int $hotelId): array
    {
        [$eligible, $context, $code] = $this->eligibility($payload['qr_token'], (int) $payload['meal_session_id'], $hotelId);

        return $this->response($eligible, $eligible ? 'Eligible for redemption.' : 'Not eligible.', $code, $context);
    }

    public function execute(array $payload, int $scannerId, int $hotelId): array
    {
        $requestHash = $this->requestHash($payload);

        return DB::transaction(function () use ($payload, $scannerId, $hotelId, $requestHash) {
            $existing = DB::table('scanner_idempotency_keys')
                ->where('hotel_id', $hotelId)
                ->where('idempotency_key', $payload['idempotency_key'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if (! hash_equals($existing->request_hash, $requestHash)) {
                    $body = $this->response(false, 'Idempotency key was already used for a different request.', RejectionCode::DUPLICATE_REQUEST->value);
                    $this->auditLogger->record('redemption.idempotency_conflict', $hotelId, $scannerId, null, ['idempotency_key' => $payload['idempotency_key']]);

                    return ['status' => 409, 'body' => $body];
                }

                return ['status' => (int) $existing->response_status, 'body' => json_decode($existing->response_body, true)];
            }

            DB::table('scanner_idempotency_keys')->insert([
                'hotel_id' => $hotelId,
                'idempotency_key' => $payload['idempotency_key'],
                'request_hash' => $requestHash,
                'response_status' => 102,
                'response_body' => json_encode(['processing' => true]),
                'expires_at' => now()->addDay(),
                'created_at' => now(),
            ]);

            [$eligible, $context, $code] = $this->eligibility($payload['qr_token'], (int) $payload['meal_session_id'], $hotelId);
            if (! $eligible) {
                $body = $this->response(false, 'Redemption rejected.', $code, $context);
                $status = $code === RejectionCode::ALREADY_REDEEMED->value ? 409 : 422;
                $rejected = $this->persistRejectedAttempt($payload, $scannerId, $hotelId, $code, $context);
                if ($rejected) {
                    $body['redemption_number'] = $rejected->redemption_number;
                    $body['rejected_redemption_id'] = $rejected->id;
                }
                $this->storeIdempotentResponse($hotelId, $payload['idempotency_key'], $status, $body);
                $this->auditLogger->record($rejected ? 'redemption.rejected_persisted' : 'redemption.rejected_audit_only', $hotelId, $scannerId, $rejected, ['rejection_code' => $code, 'device_id' => $payload['device_id'] ?? null]);

                return ['status' => $status, 'body' => $body];
            }

            /** @var MealSession $session */
            $session = $context['session'];
            $participant = Participant::withoutGlobalScope('hotel')
                ->whereKey($context['participant']->id)
                ->lockForUpdate()
                ->firstOrFail();

            $context['participant'] = $participant;

            $entitlement = ParticipantEntitlement::where('participant_id', $participant->id)
                ->where('meeting_event_id', $participant->meeting_event_id)
                ->where('entitlement_type', $session->entitlement_type->value ?? $session->entitlement_type)
                ->lockForUpdate()
                ->first();

            $alreadyRedeemed = Redemption::withoutGlobalScope('hotel')
                ->where('participant_id', $participant->id)
                ->where('meal_session_id', $session->id)
                ->whereIn('status', [RedemptionStatus::SUCCESS->value, RedemptionStatus::OVERRIDDEN->value])
                ->exists();

            if ($alreadyRedeemed) {
                $body = $this->response(false, 'Session already redeemed.', RejectionCode::ALREADY_REDEEMED->value, $context);
                $rejected = $this->persistRejectedAttempt($payload, $scannerId, $hotelId, RejectionCode::ALREADY_REDEEMED->value, $context);
                if ($rejected) {
                    $body['redemption_number'] = $rejected->redemption_number;
                    $body['rejected_redemption_id'] = $rejected->id;
                }
                $this->storeIdempotentResponse($hotelId, $payload['idempotency_key'], 409, $body);

                return ['status' => 409, 'body' => $body];
            }

            if (! $entitlement || $entitlement->remaining_quantity < 1) {
                $body = $this->response(false, 'No remaining entitlement.', RejectionCode::QUOTA_EXHAUSTED->value, $context);
                $rejected = $this->persistRejectedAttempt($payload, $scannerId, $hotelId, RejectionCode::QUOTA_EXHAUSTED->value, $context);
                if ($rejected) {
                    $body['redemption_number'] = $rejected->redemption_number;
                    $body['rejected_redemption_id'] = $rejected->id;
                }
                $this->storeIdempotentResponse($hotelId, $payload['idempotency_key'], 422, $body);

                return ['status' => 422, 'body' => $body];
            }

            try {
                $redemption = Redemption::create([
                    'hotel_id' => $hotelId,
                    'participant_id' => $participant->id,
                    'meeting_event_id' => $participant->meeting_event_id,
                    'meal_session_id' => $session->id,
                    'participant_entitlement_id' => $entitlement->id,
                    'redemption_number' => 'RDM-'.now()->format('YmdHis').'-'.Str::upper(Str::random(8)),
                    'redeemed_at' => now(),
                    'scanned_by' => $scannerId,
                    'device_id' => $payload['device_id'] ?? null,
                    'idempotency_key' => $payload['idempotency_key'],
                    'status' => RedemptionStatus::SUCCESS,
                    'metadata' => ['credential_id' => $context['credential']->id],
                    'created_at' => now(),
                ]);
            } catch (QueryException $exception) {
                if ($this->isUniqueViolation($exception)) {
                    $body = $this->response(false, 'Session already redeemed.', RejectionCode::ALREADY_REDEEMED->value, $context);
                    $rejected = $this->persistRejectedAttempt($payload, $scannerId, $hotelId, RejectionCode::ALREADY_REDEEMED->value, $context);
                    if ($rejected) {
                        $body['redemption_number'] = $rejected->redemption_number;
                        $body['rejected_redemption_id'] = $rejected->id;
                    }
                    $this->storeIdempotentResponse($hotelId, $payload['idempotency_key'], 409, $body);

                    return ['status' => 409, 'body' => $body];
                }

                throw $exception;
            }

            $entitlement->update([
                'redeemed_quantity' => $entitlement->redeemed_quantity + 1,
                'remaining_quantity' => $entitlement->remaining_quantity - 1,
            ]);

            if ($participant->status === ParticipantStatus::REGISTERED) {
                $participant->update([
                    'status' => ParticipantStatus::CHECKED_IN,
                    'checked_in_at' => now(),
                ]);
            }

            $context['entitlement'] = $entitlement->fresh();
            $context['participant'] = $participant->fresh();
            $context['redemption'] = $redemption;
            $body = $this->response(true, 'Redemption successful.', null, $context);
            $this->storeIdempotentResponse($hotelId, $payload['idempotency_key'], 200, $body);
            $this->auditLogger->record('redemption.succeeded', $hotelId, $scannerId, $redemption, ['device_id' => $payload['device_id'] ?? null]);

            return ['status' => 200, 'body' => $body];
        }, 3);
    }

    private function eligibility(string $token, int $mealSessionId, int $hotelId): array
    {
        [$valid, $credential, $qrCode] = $this->participantQRService->validate($token);
        if (! $valid) {
            return [false, [], $qrCode];
        }

        $participant = $credential->participant;
        $meeting = $participant->meetingEvent;
        $session = MealSession::withoutGlobalScope('hotel')->whereKey($mealSessionId)->first();

        if (! $session || (int) $session->hotel_id !== $hotelId || (int) $participant->hotel_id !== $hotelId) {
            return [false, compact('credential', 'participant', 'meeting'), RejectionCode::WRONG_HOTEL->value];
        }
        if ((int) $session->meeting_event_id !== (int) $meeting->id) {
            return [false, compact('credential', 'participant', 'meeting', 'session'), RejectionCode::WRONG_MEETING->value];
        }
        if ($session->status !== MealSessionStatus::OPEN) {
            return [false, compact('credential', 'participant', 'meeting', 'session'), RejectionCode::SESSION_NOT_OPEN->value];
        }
        if (($session->starts_at && now()->lessThan($session->starts_at)) || ($session->ends_at && now()->greaterThan($session->ends_at))) {
            return [false, compact('credential', 'participant', 'meeting', 'session'), RejectionCode::SESSION_EXPIRED->value];
        }

        $entitlement = ParticipantEntitlement::where('participant_id', $participant->id)
            ->where('meeting_event_id', $meeting->id)
            ->where('entitlement_type', $session->entitlement_type->value ?? $session->entitlement_type)
            ->first();

        if (! $entitlement) {
            return [false, compact('credential', 'participant', 'meeting', 'session'), RejectionCode::NO_ENTITLEMENT->value];
        }
        $duplicate = Redemption::withoutGlobalScope('hotel')
            ->where('participant_id', $participant->id)
            ->where('meal_session_id', $session->id)
            ->whereIn('status', [RedemptionStatus::SUCCESS->value, RedemptionStatus::OVERRIDDEN->value])
            ->exists();

        if ($duplicate) {
            return [false, compact('credential', 'participant', 'meeting', 'session', 'entitlement'), RejectionCode::ALREADY_REDEEMED->value];
        }

        if ($entitlement->remaining_quantity < 1) {
            return [false, compact('credential', 'participant', 'meeting', 'session', 'entitlement'), RejectionCode::QUOTA_EXHAUSTED->value];
        }

        return [true, compact('credential', 'participant', 'meeting', 'session', 'entitlement'), null];
    }

    private function response(bool $eligible, string $message, ?string $code = null, array $context = []): array
    {
        $participant = $context['participant'] ?? null;
        $meeting = $context['meeting'] ?? null;
        $session = $context['session'] ?? null;
        $entitlement = $context['entitlement'] ?? null;
        $redemption = $context['redemption'] ?? null;

        return [
            'eligible' => $eligible,
            'message' => $message,
            'rejection_code' => $code,
            'redemption_number' => $redemption?->redemption_number,
            'participant' => $participant ? ['name' => $participant->full_name, 'status' => $participant->status->value ?? $participant->status] : null,
            'meeting' => $meeting ? ['name' => $meeting->event_name, 'status' => $meeting->status->value ?? $meeting->status] : null,
            'meal_session' => $session ? ['id' => $session->id, 'name' => $session->name, 'entitlement_type' => $session->entitlement_type->value ?? $session->entitlement_type] : null,
            'remaining_entitlement' => $entitlement ? ['total' => $entitlement->total_quantity, 'redeemed' => $entitlement->redeemed_quantity, 'remaining' => $entitlement->remaining_quantity] : null,
            'scanned_at' => now()->toIso8601String(),
        ];
    }

    private function persistRejectedAttempt(array $payload, int $scannerId, int $hotelId, ?string $code, array $context): ?Redemption
    {
        if (! $code || ! in_array($code, self::OVERRIDEABLE_REJECTION_CODES, true)) {
            return null;
        }

        $participant = $context['participant'] ?? null;
        $meeting = $context['meeting'] ?? null;
        $session = $context['session'] ?? null;

        if (! $participant || ! $meeting || ! $session) {
            return null;
        }

        if ((int) $participant->hotel_id !== $hotelId || (int) $session->hotel_id !== $hotelId || (int) $meeting->hotel_id !== $hotelId) {
            return null;
        }

        $existing = Redemption::withoutGlobalScope('hotel')
            ->where('hotel_id', $hotelId)
            ->where('idempotency_key', $payload['idempotency_key'])
            ->where('status', RedemptionStatus::REJECTED->value)
            ->lockForUpdate()
            ->first();

        if ($existing) {
            return $existing;
        }

        return Redemption::create([
            'hotel_id' => $hotelId,
            'participant_id' => $participant->id,
            'meeting_event_id' => $meeting->id,
            'meal_session_id' => $session->id,
            'participant_entitlement_id' => $context['entitlement']->id ?? null,
            'redemption_number' => 'REJ-'.now()->format('YmdHis').'-'.Str::upper(Str::random(8)),
            'scanned_by' => $scannerId,
            'device_id' => $payload['device_id'] ?? null,
            'idempotency_key' => $payload['idempotency_key'],
            'status' => RedemptionStatus::REJECTED,
            'rejection_code' => $code,
            'metadata' => [
                'credential_id' => $context['credential']->id ?? null,
                'safe_persistence' => true,
            ],
            'created_at' => now(),
        ]);
    }

    private function requestHash(array $payload): string
    {
        return hash('sha256', json_encode([
            'qr_token_hash' => hash('sha256', $payload['qr_token']),
            'meal_session_id' => (string) $payload['meal_session_id'],
            'device_id' => $payload['device_id'] ?? null,
        ]));
    }

    private function storeIdempotentResponse(int $hotelId, string $key, int $status, array $body): void
    {
        DB::table('scanner_idempotency_keys')
            ->where('hotel_id', $hotelId)
            ->where('idempotency_key', $key)
            ->update(['response_status' => $status, 'response_body' => json_encode($body)]);
    }

    private function isUniqueViolation(QueryException $exception): bool
    {
        return ($exception->errorInfo[0] ?? null) === '23505';
    }
}
