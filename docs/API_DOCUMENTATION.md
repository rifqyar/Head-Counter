# API Documentation

## Public Attendance

`GET /attendance/meeting/{token}` displays the registration form after validating the hashed meeting QR token, revocation, expiration, check-in window, and meeting status.

`POST /attendance/meeting/{token}/register` registers a participant, creates meeting check-in attendance, generates entitlements, and issues a participant QR credential. Raw participant tokens are only displayed in this response.

## Scanner API

Both endpoints require Sanctum authentication, tenant middleware, web-guard `redemption.scan`, an active user, an active hotel, and the endpoint-specific token ability.

### POST /api/v1/scanner/validate

Required token ability: `scanner:validate`.

```json
{
  "qr_token": "opaque-token",
  "meal_session_id": "1",
  "device_id": "front-desk-1"
}
```

The endpoint is non-mutating. It returns participant, meeting, session, remaining entitlement, and eligibility fields. Ineligible scans return HTTP 422 with `eligible: false`.

### POST /api/v1/scanner/redeem

Required token ability: `scanner:redeem`.

```json
{
  "qr_token": "opaque-token",
  "meal_session_id": "1",
  "device_id": "front-desk-1",
  "idempotency_key": "unique-request-id"
}
```

Successful responses use HTTP 200 with `eligible: true`. Duplicate successful redemptions return HTTP 409 and `ALREADY_REDEEMED`. Same idempotency key with a different request returns HTTP 409 and `DUPLICATE_REQUEST`.

Operational scanner rejections with safe participant, meeting, session, and tenant context may include `rejected_redemption_id`. These persisted rejected records can be reviewed and overridden by authorized staff. Invalid QR, wrong-hotel, authorization, authentication, malformed, and unresolved scans remain audit-only.

## Participant QR Administration

Web routes require auth, tenant scope, `participant.qr.manage`, and participant policy checks:

```text
GET  /participants/{participant}/qr
POST /participants/{participant}/qr/generate
POST /participants/{participant}/qr/rotate
POST /participants/{participant}/qr/revoke
```

Generate and rotate show the raw participant QR token only in the immediate response. Do not place raw tokens in query strings or logs.

## Redemption Administration

`GET /redemptions` supports filters for hotel, meeting, participant, session, rejection code, status, and date range. `GET /redemptions/{redemption}` shows original/override linkage. `POST /redemptions/{redemption}/override` requires `redemption.override` and a reason. `POST /redemptions/{redemption}/reverse` requires `redemption.reverse` and a reason.
