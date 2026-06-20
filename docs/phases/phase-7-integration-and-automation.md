# Phase 7 — Integration and Automation

## Objective

Implement the external booking integration API, integration logging, queue workers, scheduler jobs, and notification system.

---

## Prerequisites

- Phase 6 complete: dashboard and reports working

---

## Tasks

### 7.1 External Booking Integration API

Create API endpoints for external booking systems:

```
POST   /api/v1/integrations/bookings
PUT    /api/v1/integrations/bookings/{external_booking_id}
GET    /api/v1/integrations/bookings/{external_booking_id}
DELETE /api/v1/integrations/bookings/{external_booking_id}/cancel
```

Implement:

- API key or HMAC authentication (each integration has a key mapped to a hotel)
- Request validation
- Idempotency support (use external_booking_id + source as idempotency key)
- Create/update/cancel booking and propagate to meeting events
- Return booking state with meeting details

### 7.2 Integration Logs

Create `integration_logs` table:

- Migration: `integration_logs` (id, hotel_id, integration_name, direction, endpoint, external_reference, request_id, status, response_code, request_payload JSONB, response_payload JSONB, error_message, started_at, completed_at)
- Log all incoming and outgoing integration requests
- Never log credentials, raw access tokens, passwords, or raw QR tokens
- Mask sensitive data in request/response payloads

### 7.3 Queue Configuration

Set up Laravel queues:

- Default queue: standard jobs
- Scanner queue: redemption processing
- Export queue: report generation
- Integration queue: external booking synchronization
- Notification queue: email notifications

Configure queue workers for:
- `php artisan queue:work --queue=default,scanner,export,integration,notification`

### 7.4 Scheduler Jobs

Create scheduled commands:

| Job | Schedule | Description |
|-----|----------|-------------|
| `meeting:open-checkin` | Every minute | Open check-in for meetings whose `checkin_open_at` has passed |
| `meeting:mark-noshow` | Every 30 minutes | Mark meetings as NO_SHOW if past `end_at` with no participants |
| `session:close-expired` | Every 5 minutes | Close meal sessions past `ends_at` |
| `qr:expire-credentials` | Every 10 minutes | Expire participant QR credentials past `expires_at` |
| `idempotency:cleanup` | Daily at 02:00 | Clean expired idempotency keys |
| `meeting:detect-overrun` | Every 15 minutes | Detect meetings running beyond schedule |
| `integration:retry-failed` | Every 30 minutes | Retry failed integrations |
| `monitor:failed-jobs` | Every 10 minutes | Alert on high failed job count |
| `qr:send-reminders` | As configured | Send QR code delivery emails for upcoming meetings |

All jobs must be:
- Idempotent
- Retry-safe
- Configured with timeout and backoff
- Log failures

### 7.5 Notification System

- Participant QR delivery by email (queued)
- Meeting reminder notifications
- Scanner failure alerts
- Integration failure alerts
- Export completion notifications

### 7.6 Integration Tests

| Test | Description |
|------|-------------|
| Create booking via API | POST creates booking and meeting events |
| Update booking via API | PUT updates booking details |
| Cancel booking via API | DELETE cancels booking and related meeting events |
| Idempotent booking | Same external_booking_id returns existing booking |
| Invalid API key | Returns 401 |
| Cross-hotel blocking | Integration key for Hotel A cannot create booking for Hotel B |
| Integration log created | Every API call creates an integration log |

---

## Completion Checklist

- [ ] Integration API endpoints working with authentication
- [ ] Integration logs recording all requests/responses
- [ ] Queue workers configured for all queues
- [ ] Scheduler jobs registered and running
- [ ] All scheduled jobs are idempotent
- [ ] Notification system sending emails
- [ ] Integration tests pass
- [ ] Code formatted

---

## Exit Criteria

Before starting Phase 8:

1. External booking API is functional and secure
2. Integration logs capture all activity
3. Queue workers process all job types
4. Scheduler runs all required jobs
5. Failed jobs are monitored