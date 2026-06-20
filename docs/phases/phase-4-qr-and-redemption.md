# Phase 4 — QR and Redemption Engine

## Objective

Implement the meeting QR code system, participant QR credentials, meal sessions, participant entitlements, and the full redemption engine with duplicate prevention, idempotency, and row-level locking.

---

## Prerequisites

- Phase 3 complete: core domain models in place, tenant isolation working, room conflicts prevented

---

## Tasks

### 4.1 Meeting QR Code

Create meeting QR lifecycle:

- Migration: add `meeting_qr_token_hash`, `checkin_open_at`, `checkin_close_at` to `meeting_events` (if not already added in Phase 3)
- Service: `MeetingQRService`
  - Generate token: 32 bytes cryptographically random
  - Store SHA-256 hash of token in `meeting_qr_token_hash`
  - Store `token_last_four` for identification
  - Generate QR code image that encodes URL: `{APP_URL}/attendance/meeting/{token}`
  - Support: activation, check-in window, expiration, revocation, regeneration
- Routes:
  - `GET /attendance/meeting/{token}` — display registration form
  - `POST /attendance/meeting/{token}/register` — submit registration
- When a QR is regenerated, immediately invalidate the old token (new hash)
- Never log raw meeting QR tokens
- Printable QR output: generate PNG/SVG for download

### 4.2 Participant QR Credentials

Create `participant_qr_credentials` table:

- Migration: `participant_qr_credentials` (id, participant_id, token_hash, token_last_four, status, issued_at, expires_at, revoked_at, revoked_by, timestamps)
- Enum: `QRCredentialStatus` (ACTIVE, EXPIRED, REVOKED)
- Model: `App\Domain\QRCode\ParticipantQRCredential`
- Service: `ParticipantQRService`
  - Generate credential: 32-byte random token, store hash
  - QR URL: `{APP_URL}/scan/participant/{opaque-token}`
  - Rotation: revoke old, generate new, log action
  - Revocation: mark status REVOKED, set revoked_at and revoked_by
  - Validation: check status, expiration, meeting status
- Never store raw token; never expose participant database ID in QR payload

### 4.3 Meal Sessions

Create `meal_sessions` table:

- Migration: `meal_sessions` (id, hotel_id, meeting_event_id, entitlement_type, session_number, name, starts_at, ends_at, status, location, created_by, updated_by, timestamps)
- Enum: `MealSessionStatus` (DRAFT, OPEN, CLOSED, CANCELLED)
- Model: `App\Domain\Catering\MealSession`
- Service: `MealSessionService`
  - Create sessions from package entitlements when meeting is scheduled
  - Open/close sessions
  - Prevent scanning against closed or draft sessions
- Example: Half Day Package creates "Coffee Break 1" and "Lunch" sessions; Full Day Package creates "Coffee Break 1", "Lunch", "Coffee Break 2" sessions

### 4.4 Participant Entitlements

Create `participant_entitlements` table:

- Migration: `participant_entitlements` (id, participant_id, meeting_event_id, entitlement_type, total_quantity, redeemed_quantity, remaining_quantity, timestamps)
- Model: `App\Domain\Redemption\ParticipantEntitlement`
- Generated from package entitlements upon participant registration
- Validation: `remaining_quantity = total_quantity - redeemed_quantity` (enforce via CHECK constraint or application validation)
- When package changes: synchronization process that preserves redemption history and does not reduce below already redeemed quantities

### 4.5 Redemption Engine

Create `redemptions` table:

- Migration: `redemptions` (id, hotel_id, participant_id, meeting_event_id, meal_session_id, participant_entitlement_id, redemption_number, redeemed_at, scanned_by, device_id, idempotency_key, status, rejection_code, override_reason, metadata JSONB, created_at)
- Enum: `RedemptionStatus` (SUCCESS, REJECTED, REVERSED, OVERRIDDEN)
- Enum: `RejectionCode` (INVALID_QR, QR_EXPIRED, QR_REVOKED, PARTICIPANT_BLOCKED, WRONG_HOTEL, WRONG_MEETING, SESSION_NOT_OPEN, SESSION_EXPIRED, NO_ENTITLEMENT, ALREADY_REDEEMED, QUOTA_EXHAUSTED, DUPLICATE_REQUEST, MEETING_CANCELLED, MEETING_COMPLETED)
- Model: `App\Domain\Redemption\Redemption`
- Unique partial index: successful redemption per `participant_id + meal_session_id`

### 4.6 Scanner API Endpoints

Create scanner API:

```
POST /api/v1/scanner/validate
POST /api/v1/scanner/redeem
```

Payload:
```json
{
  "qr_token": "opaque-token",
  "meal_session_id": "uuid",
  "device_id": "device-identifier",
  "idempotency_key": "unique-request-id"
}
```

### 4.7 Redemption Flow (Action Class)

Create `RedeemParticipantAction` implementing the full 22-step validation flow:

1. Authenticate scanner operator
2. Validate operator role and permission (`redemption.scan`)
3. Resolve operator hotel
4. Validate participant QR token (hash lookup)
5. Resolve participant
6. Validate QR status (ACTIVE)
7. Validate QR expiration
8. Validate participant status (not BLOCKED or CANCELLED)
9. Validate meeting status (not CANCELLED or COMPLETED)
10. Validate meal session belongs to same meeting
11. Validate meal session belongs to same hotel
12. Validate meal session is OPEN
13. Validate scan-time window
14. Validate participant has entitlement for this type
15. Validate remaining quantity > 0
16. Validate participant has not already redeemed this session
17. Validate idempotency key (return cached result if duplicate)
18. Lock entitlement row (`lockForUpdate()`)
19. Create redemption record
20. Update entitlement counters safely
21. Write audit log entry
22. Return standardized response

All inside a `DB::transaction()` with `lockForUpdate()` on the entitlement row.

### 4.8 Scanner Idempotency

Create `scanner_idempotency_keys` table:

- Migration: `scanner_idempotency_keys` (id, hotel_id, idempotency_key, request_hash, response_status, response_body JSONB, expires_at, created_at)
- Before processing a redemption, check if idempotency_key exists with same request_hash
- If found, return cached response
- If not found, process and store response
- Clean up expired keys via scheduled command

### 4.8 Override and Reversal

- Override redemption: requires `redemption.override` permission, reason required, record original and final state
- Reverse redemption: mark as REVERSED, restore entitlement quantity, record auditor
- Both actions write to audit log

### 4.9 Scanner UI

Create a scanner web page optimized for hotel staff:

- Simple, fast, high-contrast design
- Green for success, Red for failure, Yellow for warning
- Display: participant name, meeting name, session name, scan time, remaining entitlement, rejection reason
- Sound or vibration feedback when supported
- Mobile-friendly

### 4.10 Concurrency Tests

| Test | Description |
|------|-------------|
| Concurrent redemption | Two simultaneous requests for same participant + session; exactly one succeeds |
| Idempotent retry | Same idempotency_key returns same response |
| Expired QR | QR past expiration is rejected |
| Revoked QR | Revoked QR is rejected |
| Blocked participant | Blocked participant cannot redeem |
| Wrong hotel | Cross-hotel redemption is rejected |
| Wrong meeting | Redemption for wrong meeting is rejected |
| Closed session | Closed meal session rejects redemption |
| No entitlement | Redemption without entitlement is rejected |
| Already redeemed | Duplicate session redemption is rejected |
| Override | Override requires permission and reason |
| Reversal | Reversal restores entitlement |

---

## Completion Checklist

- [ ] Meeting QR generation, validation, revocation, regeneration working
- [ ] Participant QR generation, validation, expiration, revocation working
- [ ] Meal session creation from package entitlements
- [ ] Participant entitlements generated on registration
- [ ] Full redemption flow with 22-step validation
- [ ] Duplicate redemption prevented (database constraint)
- [ ] Idempotency working
- [ ] Override and reversal working
- [ ] Scanner API returning standardized responses
- [ ] Scanner UI functional
- [ ] Concurrency tests pass
- [ ] Audit logging for all redemption events
- [ ] Code formatted

---

## Exit Criteria

Before starting Phase 5:

1. Meeting QR codes can be generated, displayed, and validated
2. Participants can register and receive unique QR credentials
3. Scanner operators can validate and redeem participant QR codes
4. A participant cannot redeem the same session twice
5. A participant can only redeem included entitlements
6. Concurrent scanning is safe (tested)
7. Idempotent retry returns same result
8. All redemption events are auditable