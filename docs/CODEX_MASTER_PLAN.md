# Codex Implementation Prompt — Enterprise Hotel Meeting Headcounter

## Project Context

You are working inside an existing Laravel application repository for a hotel meeting-room headcounter system.

The current application:

- Already exists and must be inspected before major changes are made.
- Uses Laravel 10 or a nearby Laravel version.
- Currently uses MySQL.
- Is partially implemented.
- Must be completed, stabilized, refactored, and upgraded into an enterprise-grade, production-ready system.
- Must be migrated from MySQL to PostgreSQL.
- Must retain existing working features whenever possible.
- Must not be rebuilt from scratch unless the existing architecture is objectively unusable and the reason is documented.

Work directly from the actual repository available in the IDE. Do not assume that existing table names, routes, controllers, services, frontend frameworks, or module boundaries match the target design in this document. Inspect the codebase first and adapt the target architecture to the real project.

The final system must be secure, maintainable, modular, testable, observable, scalable, and ready for real hotel operations.

---

# 1. Core Business Flow

The application manages hotel meeting-room usage, participant attendance, meeting packages, coffee-break and meal entitlements, QR generation, and QR redemption.

The primary flow is:

1. A client books a meeting room through another application or external booking system.
2. The booking data is entered into or synchronized with this Headcounter application.
3. A hotel administrator assigns:
   - The client or company.
   - The meeting room.
   - The meeting date.
   - The meeting start and end time.
   - The expected number of participants.
   - The selected meeting package.
   - The number and types of coffee breaks.
   - Lunch, dinner, snacks, or other benefits.
   - Additional facilities when required.
4. The system validates meeting-room availability and prevents overlapping schedules.
5. When the meeting begins, the meeting status changes to `OCCUPIED`, and the assigned room reflects its active operational state.
6. The system generates a printable meeting QR code.
7. Participants scan the meeting QR code to register and submit their attendance.
8. After successful registration, each participant receives a unique participant QR code.
9. The participant QR code is scanned by hotel staff during coffee breaks, lunch, dinner, or another included session.
10. A participant may only redeem benefits included in the selected package.
11. A participant may not redeem the same meal session more than once.
12. A package containing one coffee break and one lunch allows one coffee-break redemption and one lunch redemption.
13. A package containing only one coffee break allows only one redemption for that coffee-break session.
14. A package containing two coffee breaks and one lunch allows one redemption in each applicable session.
15. Every attendance, scan, rejection, override, reversal, and status change must be recorded for audit and reporting.

Do not implement QR usage as a generic total scan counter. Redemption must be based on meeting package entitlements and specific meal sessions.

---

# 2. Main Objectives

Upgrade the system so it is:

- Enterprise-ready.
- Production-ready.
- Secure by default.
- Modular and maintainable.
- Testable.
- Scalable.
- PostgreSQL-compatible.
- Multi-hotel ready.
- Protected against duplicate attendance and double redemption.
- Protected against room schedule conflicts.
- Protected against concurrent scanner race conditions.
- Fully auditable.
- Equipped with role-based access control.
- Equipped with structured logging and monitoring.
- Documented for development, deployment, operation, backup, and recovery.

---

# 3. Mandatory Initial Codebase Audit

Before implementing major changes, inspect the entire repository.

Audit at least the following:

- Laravel version.
- PHP version.
- Composer dependencies.
- Frontend framework and build tooling.
- Application folder structure.
- Web routes.
- API routes.
- Controllers.
- Models.
- Form Requests.
- Middleware.
- Services.
- Repositories.
- Actions.
- Policies.
- Events and listeners.
- Jobs and queues.
- Scheduler.
- Database migrations.
- Seeders.
- Factories.
- Authentication.
- Authorization.
- Existing QR-code implementation.
- Existing attendance flow.
- Existing scanner flow.
- Existing booking flow.
- Existing meeting-room assignment flow.
- Existing report and export functions.
- External integrations.
- Environment configuration.
- Logging.
- Exception handling.
- Automated tests.
- Deployment configuration.
- Docker files, if any.

Identify:

- Existing completed features.
- Incomplete or placeholder features.
- Broken flows.
- Security vulnerabilities.
- Mass-assignment risks.
- SQL-injection risks.
- Insecure direct object references.
- Cross-tenant data exposure.
- N+1 queries.
- Slow queries.
- Missing indexes.
- Race conditions.
- Duplicate attendance risks.
- Double-scan risks.
- Room schedule conflicts.
- Timezone inconsistencies.
- Unhandled exceptions.
- Hardcoded credentials.
- MySQL-specific SQL.
- MySQL-specific schema definitions.
- Breaking API inconsistencies.
- Missing validation.
- Missing tests.
- Missing audit logging.
- Technical debt.

Create:

```text
docs/CODEBASE_AUDIT.md
```

The audit document must include:

- Current architecture.
- Current technology versions.
- Current module inventory.
- Features that already work.
- Features that are incomplete.
- Bugs found.
- Security risks.
- Data-integrity risks.
- PostgreSQL incompatibilities.
- Performance risks.
- Technical debt.
- Recommended target architecture.
- Proposed phased roadmap.
- Priority classification.
- Files or modules expected to change.
- Backward-compatibility concerns.

Do not perform a blind rewrite before this audit is complete.

---

# 4. MySQL to PostgreSQL Migration

Migrate the application database from MySQL to PostgreSQL.

Target environment example:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=headcounter
DB_USERNAME=headcounter
DB_PASSWORD=change_me
```

Audit and replace MySQL-specific features, including but not limited to:

- `ENUM`.
- `UNSIGNED`.
- `TINYINT`.
- `IFNULL`.
- `DATE_FORMAT`.
- `GROUP_CONCAT`.
- `FIND_IN_SET`.
- `ON DUPLICATE KEY UPDATE`.
- Backtick identifiers.
- MySQL-only JSON functions.
- MySQL-specific collations.
- Case-insensitive comparisons that depend on MySQL collation.
- Raw SQL written specifically for MySQL.
- Non-portable auto-increment assumptions.
- Boolean values stored as integers without a justified compatibility reason.
- MySQL-only full-text behavior.
- MySQL-only date functions.

Use appropriate PostgreSQL types:

- `uuid`.
- `bigint`.
- `boolean`.
- `date`.
- `time`.
- `timestamp with time zone`.
- `jsonb`.
- `numeric`.
- `text`.
- `varchar`.

Use UUIDs for major domain entities when practical and when backward compatibility can be preserved.

Potential UUID entities:

- Hotels.
- Meeting rooms.
- Clients.
- Bookings.
- Meeting events.
- Packages.
- Participants.
- Attendance records.
- QR credentials.
- Meal sessions.
- Entitlements.
- Redemptions.
- Audit logs.
- Integration logs.

When legacy numeric IDs are still required, preserve them using fields such as:

```text
legacy_id
external_id
```

Add proper:

- Foreign keys.
- Unique constraints.
- Check constraints.
- Partial indexes.
- Composite indexes.
- Transactions.
- Data validation.
- Referential integrity.

Important indexes should cover:

- `hotel_id`.
- `meeting_room_id`.
- Meeting dates.
- `start_at`.
- `end_at`.
- Booking status.
- Meeting status.
- External booking references.
- Participant lookups.
- QR token hashes.
- Meal sessions.
- Entitlements.
- Redemptions.
- Scanner idempotency keys.

Create:

```text
docs/MYSQL_TO_POSTGRESQL_MIGRATION.md
```

The document must include:

- Data-type mapping.
- Schema differences.
- SQL functions replaced.
- Raw queries changed.
- Migration execution order.
- Data export and import process.
- Record-count validation.
- Foreign-key validation.
- Duplicate-data handling.
- Rollback strategy.
- Backup requirements.
- Maintenance-window strategy.
- Zero-downtime considerations where realistic.
- Post-migration verification queries.

Create data migration scripts when required. Do not rely only on schema migrations if production data already exists.

---

# 5. Application Architecture

Use a modular Laravel architecture while preserving Laravel conventions.

Do not place complex business logic inside controllers.

A possible target structure is:

```text
app/
├── Domain/
│   ├── Hotel/
│   ├── Booking/
│   ├── Meeting/
│   ├── Participant/
│   ├── Attendance/
│   ├── Catering/
│   ├── QRCode/
│   ├── Redemption/
│   ├── Reporting/
│   └── Integration/
├── Actions/
├── Services/
├── DTOs/
├── Enums/
├── Policies/
├── Events/
├── Listeners/
├── Jobs/
├── Exceptions/
└── Support/
```

Controllers should primarily:

- Receive requests.
- Invoke Form Request validation.
- Invoke an Action or Service.
- Return a Resource or standardized response.

Use where appropriate:

- Form Request classes.
- API Resources.
- Policies.
- Gates.
- Services.
- Action classes.
- DTOs.
- Domain exceptions.
- Database transactions.
- Events and listeners.
- Queued jobs.
- Cache.
- Enums.
- State-transition guards.
- Repository abstractions only when they add real value.

Avoid overengineering. The architecture must remain understandable to a normal Laravel development team.

---

# 6. Multi-Hotel and Tenant Isolation

Prepare the system for one or multiple hotels.

Create or standardize:

```text
hotels
```

All operational data must be associated with a hotel, including:

- Users.
- Meeting rooms.
- Clients.
- Bookings.
- Meetings.
- Packages.
- Participants.
- Attendance records.
- Meal sessions.
- Entitlements.
- Redemptions.
- Reports.
- Audit logs.
- Integration logs.

A user from Hotel A must never access Hotel B data unless explicitly authorized as a platform-level administrator.

Do not trust `hotel_id` directly from request payloads.

Tenant context must come from:

- The authenticated user.
- A server-side tenant context.
- An approved super-admin hotel switch.
- A verified integration credential mapped to a hotel.

Use policies, middleware, scoped queries, or another safe tenant-isolation mechanism.

Add automated tests for cross-hotel isolation.

---

# 7. Roles and Permissions

Implement role-based access control.

Minimum roles:

```text
SUPER_ADMIN
HOTEL_ADMIN
SALES_ADMIN
BANQUET_ADMIN
FRONT_OFFICE
MEETING_OPERATOR
SCANNER_OPERATOR
REPORT_VIEWER
AUDITOR
```

Suggested permissions:

```text
hotel.manage
user.manage
role.manage
permission.manage
meeting_room.view
meeting_room.manage
client.view
client.manage
booking.view
booking.create
booking.update
booking.cancel
meeting.view
meeting.create
meeting.update
meeting.assign_room
meeting.start
meeting.complete
meeting.cancel
participant.view
participant.register
participant.update
participant.block
attendance.view
attendance.scan
meal_package.view
meal_package.manage
meal_session.view
meal_session.manage
redemption.view
redemption.scan
redemption.override
redemption.reverse
report.view
report.export
audit.view
integration.manage
settings.manage
```

All protected endpoints and UI actions must enforce authorization server-side.

A redemption override must:

- Require a dedicated permission.
- Require a reason.
- Record the acting user.
- Record the original state.
- Record the final state.
- Be visible in the audit trail.

---

# 8. Target Data Model

Adapt the following model to the actual repository.

## 8.1 Hotels

```text
hotels
- id
- code
- name
- address
- timezone
- status
- settings jsonb
- created_at
- updated_at
```

## 8.2 Meeting Rooms

```text
meeting_rooms
- id
- hotel_id
- code
- name
- floor
- capacity
- operational_status
- facilities jsonb
- created_at
- updated_at
```

Room operational statuses:

```text
AVAILABLE
RESERVED
OCCUPIED
CLEANING
MAINTENANCE
INACTIVE
```

Do not use the persisted room status as the only source of truth when occupancy can be derived from an active meeting. Keep operational status synchronized through controlled state transitions.

## 8.3 Clients

```text
clients
- id
- hotel_id
- external_id
- company_name
- contact_name
- contact_email
- contact_phone
- billing_address
- tax_number
- metadata jsonb
- created_at
- updated_at
```

## 8.4 Bookings

```text
bookings
- id
- hotel_id
- external_booking_id
- client_id
- booking_number
- booking_source
- booking_date
- status
- notes
- created_by
- updated_by
- created_at
- updated_at
```

Booking statuses:

```text
DRAFT
CONFIRMED
CANCELLED
COMPLETED
```

Recommended unique key:

```text
hotel_id + booking_source + external_booking_id
```

## 8.5 Meeting Events

```text
meeting_events
- id
- hotel_id
- booking_id
- meeting_room_id
- event_name
- event_date
- start_at
- end_at
- expected_participants
- actual_participants
- status
- meeting_qr_token_hash
- checkin_open_at
- checkin_close_at
- started_at
- completed_at
- cancelled_at
- created_by
- updated_by
- created_at
- updated_at
```

Meeting statuses:

```text
DRAFT
SCHEDULED
CHECKIN_OPEN
OCCUPIED
COMPLETED
CANCELLED
NO_SHOW
```

## 8.6 Meeting Packages

```text
meeting_packages
- id
- hotel_id
- code
- name
- description
- price
- is_active
- metadata jsonb
- created_at
- updated_at
```

## 8.7 Package Entitlements

Do not store only one generic maximum scan count.

```text
package_entitlements
- id
- package_id
- entitlement_type
- quantity
- metadata jsonb
- created_at
- updated_at
```

Entitlement types:

```text
COFFEE_BREAK
LUNCH
DINNER
SNACK
WELCOME_DRINK
CUSTOM
```

Examples:

```text
Half Day Package:
- COFFEE_BREAK = 1
- LUNCH = 1

Full Day Package:
- COFFEE_BREAK = 2
- LUNCH = 1
```

## 8.8 Meeting Package Assignments

```text
meeting_package_assignments
- id
- meeting_event_id
- package_id
- participant_quota
- unit_price
- notes
- created_at
- updated_at
```

Support multiple packages per meeting if the existing business process requires different participant groups.

## 8.9 Participants

```text
participants
- id
- hotel_id
- meeting_event_id
- participant_number
- full_name
- company_name
- email
- phone
- identity_reference
- registration_source
- status
- registered_at
- checked_in_at
- metadata jsonb
- created_at
- updated_at
```

Participant statuses:

```text
REGISTERED
CHECKED_IN
CANCELLED
BLOCKED
```

Use flexible duplicate detection, such as:

- Meeting event plus normalized email.
- Meeting event plus normalized phone.
- Meeting event plus identity reference.

Do not block valid participants who do not have email or phone data.

## 8.10 Participant QR Credentials

```text
participant_qr_credentials
- id
- participant_id
- token_hash
- token_last_four
- status
- issued_at
- expires_at
- revoked_at
- revoked_by
- created_at
- updated_at
```

QR credential statuses:

```text
ACTIVE
EXPIRED
REVOKED
```

Do not store the raw token unless absolutely required. Prefer a cryptographic hash.

The QR payload must not expose the participant database ID.

## 8.11 Meeting Attendance

```text
meeting_attendances
- id
- meeting_event_id
- participant_id
- attendance_type
- attended_at
- verification_method
- device_id
- scanned_by
- metadata jsonb
- created_at
```

Attendance types:

```text
MEETING_CHECKIN
MEETING_CHECKOUT
```

Prevent accidental duplicate check-in records where appropriate.

## 8.12 Meal Sessions

Create actual sessions for each meeting.

```text
meal_sessions
- id
- hotel_id
- meeting_event_id
- entitlement_type
- session_number
- name
- starts_at
- ends_at
- status
- location
- created_by
- updated_by
- created_at
- updated_at
```

Examples:

```text
Coffee Break 1
Lunch
Coffee Break 2
Dinner
```

Meal-session statuses:

```text
DRAFT
OPEN
CLOSED
CANCELLED
```

## 8.13 Participant Entitlements

Generate participant entitlements from the assigned package.

```text
participant_entitlements
- id
- participant_id
- meeting_event_id
- entitlement_type
- total_quantity
- redeemed_quantity
- remaining_quantity
- created_at
- updated_at
```

Enforce valid quantity relationships using application validation and database constraints.

## 8.14 Redemptions

```text
redemptions
- id
- hotel_id
- participant_id
- meeting_event_id
- meal_session_id
- participant_entitlement_id
- redemption_number
- redeemed_at
- scanned_by
- device_id
- idempotency_key
- status
- rejection_code
- override_reason
- metadata jsonb
- created_at
```

Statuses:

```text
SUCCESS
REJECTED
REVERSED
OVERRIDDEN
```

Prevent successful duplicate redemption for the same participant and session.

Use a suitable PostgreSQL unique constraint or partial unique index, for example a unique successful redemption for:

```text
participant_id + meal_session_id
```

Do not delete redemption history when reversing a redemption.

## 8.15 Scanner Idempotency

Create an idempotency storage mechanism if needed:

```text
scanner_idempotency_keys
- id
- hotel_id
- idempotency_key
- request_hash
- response_status
- response_body jsonb
- expires_at
- created_at
```

A retried request with the same idempotency key and equivalent payload must return the previous result without creating a duplicate redemption.

---

# 9. Meeting-Room Conflict Prevention

A meeting conflicts when:

```text
existing.start_at < requested.end_at
AND existing.end_at > requested.start_at
```

Ignore inactive statuses such as:

```text
CANCELLED
NO_SHOW
```

Perform conflict protection at two levels:

1. Application-level validation for clear user feedback.
2. Database-level protection for data integrity.

Use PostgreSQL range and exclusion constraints where appropriate.

Example concept:

```sql
CREATE EXTENSION IF NOT EXISTS btree_gist;
```

```sql
EXCLUDE USING gist (
    meeting_room_id WITH =,
    tstzrange(start_at, end_at, '[)') WITH &&
)
```

Apply the constraint only to active meeting statuses using a compatible partial constraint or another safe database design.

When editing a meeting, exclude the current record from application-level conflict checking.

Return a clear conflict message:

```text
The meeting room is already assigned during the requested time.
Conflicting event: [event name], [start time] - [end time].
```

Add tests for:

- Exact overlap.
- Partial overlap.
- Contained overlap.
- Adjacent meetings where one ends exactly when another begins.
- Cancelled meeting.
- Editing the same meeting.
- Concurrent meeting creation.

---

# 10. Meeting QR Code

Each meeting must have a public QR code used to open the participant attendance page.

The QR payload must not contain:

- Raw database IDs.
- Client-sensitive information.
- Internal booking information.
- Easily manipulated parameters.

Use:

- A cryptographically random opaque token, or
- A signed URL with expiration and revocation support.

Example routes:

```text
GET /attendance/meeting/{token}
POST /attendance/meeting/{token}/register
```

The meeting QR must support:

- Activation.
- Check-in start time.
- Check-in closing time.
- Expiration.
- Revocation.
- Regeneration.
- Rate limiting.
- Meeting status validation.
- Printable output.
- Preview.
- Download.

When regenerated, the old token must immediately become invalid.

Never log the raw meeting QR token.

---

# 11. Participant Registration and Attendance

The public attendance page must be mobile-friendly.

Minimum fields:

```text
Full name
Company
Email
Phone number
```

Allow hotel-configurable fields when practical.

Registration flow:

1. Validate the meeting QR.
2. Validate that the meeting exists.
3. Validate the meeting hotel.
4. Validate the meeting status.
5. Validate the check-in window.
6. Validate that the meeting is not completed or cancelled.
7. Validate participant quota.
8. Apply duplicate-participant detection.
9. Create or update the participant according to the approved business rule.
10. Create the attendance record.
11. Generate entitlements from the meeting package.
12. Generate the participant QR credential.
13. Return or display the participant QR.
14. Optionally queue QR delivery by email.

All critical operations must run inside one database transaction.

If any critical step fails, roll back the transaction.

Support configurable over-quota behavior:

```text
BLOCK
ALLOW_WITH_WARNING
REQUIRE_ADMIN_APPROVAL
```

Record the selected behavior and any approval in the audit log.

---

# 12. Participant QR Code

Each participant must receive a unique and secure QR code.

Use:

- At least 32 bytes of cryptographically secure random data, or
- A secure signed token design.

Example QR URL:

```text
https://example.com/scan/participant/{opaque-token}
```

The QR must support:

- Token hashing.
- Expiration.
- Revocation.
- Rotation.
- Meeting association.
- Participant association.
- Active status validation.

When an administrator regenerates a participant QR:

- Revoke the old credential.
- Generate a new credential.
- Record the action.
- Never expose old raw tokens.

---

# 13. Coffee-Break and Meal Scanning

Create scanner endpoints similar to:

```text
POST /api/v1/scanner/validate
POST /api/v1/scanner/redeem
```

Example payload:

```json
{
  "qr_token": "opaque-token",
  "meal_session_id": "uuid",
  "device_id": "device-identifier",
  "idempotency_key": "unique-request-id"
}
```

The redemption flow must:

1. Authenticate the scanner operator.
2. Validate the operator role and permission.
3. Resolve the operator hotel.
4. Validate the participant QR token.
5. Resolve the participant.
6. Validate QR status.
7. Validate QR expiration.
8. Validate participant status.
9. Validate meeting status.
10. Validate that the meal session belongs to the same meeting.
11. Validate that the meal session belongs to the same hotel.
12. Validate that the meal session is `OPEN`.
13. Validate the configured scan-time window.
14. Validate the participant entitlement.
15. Validate remaining quantity.
16. Validate that the participant has not redeemed the same session.
17. Validate the idempotency key.
18. Lock the entitlement or relevant redemption scope.
19. Create the redemption record.
20. Update entitlement counters safely.
21. Write the audit log.
22. Return a standardized response.

Use a database transaction and PostgreSQL row-level locking.

With Eloquent, use an appropriate approach such as:

```php
lockForUpdate()
```

The design must remain safe when two scanners submit the same participant QR at almost the same time.

Example successful response:

```json
{
  "success": true,
  "message": "Redemption completed successfully.",
  "data": {
    "participant_name": "John Doe",
    "meeting_name": "Annual Meeting",
    "session_name": "Lunch",
    "redeemed_at": "2026-06-19T12:10:00+07:00",
    "remaining_entitlement": {
      "coffee_break": 1,
      "lunch": 0
    }
  },
  "meta": {
    "request_id": "uuid"
  }
}
```

Required rejection codes include:

```text
INVALID_QR
QR_EXPIRED
QR_REVOKED
PARTICIPANT_BLOCKED
WRONG_HOTEL
WRONG_MEETING
SESSION_NOT_OPEN
SESSION_EXPIRED
NO_ENTITLEMENT
ALREADY_REDEEMED
QUOTA_EXHAUSTED
DUPLICATE_REQUEST
MEETING_CANCELLED
MEETING_COMPLETED
```

Example rejected response:

```json
{
  "success": false,
  "code": "ALREADY_REDEEMED",
  "message": "The participant has already redeemed the Lunch session.",
  "data": {
    "participant_name": "John Doe",
    "redeemed_at": "2026-06-19T12:02:11+07:00"
  },
  "meta": {
    "request_id": "uuid"
  }
}
```

The scanner user interface must clearly display:

- Green for success.
- Red for failure.
- Yellow for warning.
- Participant name.
- Meeting name.
- Session name.
- Scan time.
- Remaining entitlement.
- Rejection reason.
- Sound or vibration feedback when supported.

---

# 14. Session-Based Redemption Rules

Redemption must be based on both entitlement type and actual session.

## Package A

```text
Coffee Break = 1
Lunch = 1
```

Allowed:

```text
Coffee Break 1: once
Lunch: once
```

Not allowed:

```text
Coffee Break 1: twice
```

## Package B

```text
Coffee Break = 2
Lunch = 1
```

Expected sessions:

```text
Coffee Break 1
Lunch
Coffee Break 2
```

The participant may redeem once in each session.

## Package C

```text
Lunch = 1
```

The participant must not redeem a coffee-break session.

Support package changes before the meeting starts.

If a package changes after participants are already registered:

- Provide a safe entitlement synchronization process.
- Preserve previous redemption history.
- Do not reduce entitlement below already redeemed quantities.
- Require confirmation when the change creates inconsistency.
- Record before and after state.
- Record the acting user.
- Record the reason.

---

# 15. Meeting and Room Lifecycle

Recommended meeting lifecycle:

```text
DRAFT
→ SCHEDULED
→ CHECKIN_OPEN
→ OCCUPIED
→ COMPLETED
```

Alternative flows:

```text
SCHEDULED → CANCELLED
SCHEDULED → NO_SHOW
CHECKIN_OPEN → CANCELLED
```

When a meeting becomes `OCCUPIED`:

- Set `started_at`.
- Update or synchronize the room operational state.
- Publish `MeetingStarted`.
- Record an audit event.

When a meeting becomes `COMPLETED`:

- Set `completed_at`.
- Close remaining open meal sessions.
- Prevent new participant registration.
- Expire or invalidate the meeting QR.
- Move the room to `CLEANING` when configured.
- Publish `MeetingCompleted`.
- Record an audit event.

Room lifecycle example:

```text
AVAILABLE
→ RESERVED
→ OCCUPIED
→ CLEANING
→ AVAILABLE
```

Maintenance flow:

```text
AVAILABLE → MAINTENANCE → AVAILABLE
```

Do not allow arbitrary state changes. Implement explicit transition guards.

---

# 16. External Booking Integration

Provide an integration API for external booking systems.

Possible endpoints:

```text
POST /api/v1/integrations/bookings
PUT /api/v1/integrations/bookings/{external_booking_id}
GET /api/v1/integrations/bookings/{external_booking_id}
POST /api/v1/integrations/bookings/{external_booking_id}/cancel
```

Use one or more suitable security mechanisms:

- API key.
- HMAC signature.
- OAuth client credentials.
- IP allowlist as an additional layer, not the only layer.

Support:

- Idempotency.
- External booking ID.
- Source-system identification.
- Request ID.
- Request validation.
- Request and response logging.
- Sensitive-data masking.
- Retry-safe processing.
- Duplicate prevention.

Create:

```text
integration_logs
- id
- hotel_id
- integration_name
- direction
- endpoint
- external_reference
- request_id
- status
- response_code
- request_payload jsonb
- response_payload jsonb
- error_message
- started_at
- completed_at
```

Never log credentials, raw access tokens, passwords, or raw QR tokens.

---

# 17. Standard API Responses

Standardize all API responses.

Successful response:

```json
{
  "success": true,
  "message": "The request was processed successfully.",
  "data": {},
  "meta": {
    "request_id": "uuid"
  }
}
```

Error response:

```json
{
  "success": false,
  "code": "VALIDATION_ERROR",
  "message": "The submitted data is invalid.",
  "errors": {
    "field": [
      "Validation message."
    ]
  },
  "meta": {
    "request_id": "uuid"
  }
}
```

Create a reusable response factory, helper, or responder class.

Use correct HTTP status codes:

```text
200 OK
201 Created
202 Accepted
204 No Content
400 Bad Request
401 Unauthorized
403 Forbidden
404 Not Found
409 Conflict
422 Unprocessable Entity
429 Too Many Requests
500 Internal Server Error
503 Service Unavailable
```

Preserve backward compatibility for existing consumers where required. If an endpoint contract must change, document the compatibility plan.

---

# 18. Security Requirements

Implement or verify:

- Laravel Sanctum, session authentication, or the existing approved authentication method.
- CSRF protection for web requests.
- Strong password hashing.
- Rate limiting.
- Brute-force protection.
- Secure session settings.
- Server-side authorization.
- Input validation.
- Output escaping.
- SQL-injection prevention.
- Mass-assignment protection.
- File-upload validation.
- Secure QR tokens.
- Token rotation.
- Token revocation.
- Audit logging.
- CORS allowlist.
- Environment-based secrets.
- Secure cookies.
- Production exception masking.
- HTTPS enforcement in production.
- Security headers.

Recommended headers:

```text
Content-Security-Policy
X-Content-Type-Options
Referrer-Policy
Permissions-Policy
Strict-Transport-Security
```

Do not:

- Commit credentials.
- Hardcode secrets.
- Log passwords.
- Log raw QR tokens.
- Log access tokens.
- Expose stack traces in production.
- Trust authorization decisions from the frontend.
- Trust tenant IDs from request payloads.

---

# 19. Audit Trail

Create:

```text
audit_logs
- id
- hotel_id
- actor_type
- actor_id
- action
- entity_type
- entity_id
- before_data jsonb
- after_data jsonb
- metadata jsonb
- ip_address
- user_agent
- request_id
- created_at
```

Audit at least:

- Login.
- Logout.
- Booking creation.
- Booking update.
- Booking cancellation.
- Room assignment.
- Schedule change.
- Meeting start.
- Meeting completion.
- Meeting cancellation.
- Participant quota change.
- Participant registration.
- Participant update.
- Participant blocking.
- Meeting QR regeneration.
- Meeting QR revocation.
- Participant QR regeneration.
- Participant QR revocation.
- Meal-session opening.
- Meal-session closing.
- Redemption success.
- Redemption rejection.
- Redemption override.
- Redemption reversal.
- Package changes.
- Entitlement synchronization.
- Report export.
- User changes.
- Role changes.
- Permission changes.
- Integration failures.
- Sensitive settings changes.

Audit logs must not be editable through the normal application UI.

---

# 20. Operational Dashboard

Create an operational hotel dashboard showing:

- Meetings today.
- Upcoming meetings.
- Available rooms.
- Reserved rooms.
- Occupied rooms.
- Rooms being cleaned.
- Rooms under maintenance.
- Expected participants.
- Registered participants.
- Checked-in participants.
- Attendance percentage.
- Coffee-break entitlement count.
- Coffee-break redeemed count.
- Lunch entitlement count.
- Lunch redeemed count.
- Successful redemptions.
- Rejected scans.
- Meetings starting soon.
- Meetings running beyond schedule.
- Room-conflict warnings.
- Participant over-capacity warnings.
- Currently open meal sessions.
- Recent scanner failures.

Filters:

- Hotel.
- Date.
- Date range.
- Meeting room.
- Client.
- Meeting.
- Status.

All calculations must use the hotel timezone.

---

# 21. Reporting

Implement the following reports.

## 21.1 Meeting Report

Include:

- Booking number.
- Client.
- Meeting name.
- Room.
- Date.
- Start time.
- End time.
- Expected participants.
- Actual participants.
- Attendance percentage.
- Package.
- Status.

## 21.2 Participant Attendance Report

Include:

- Participant.
- Company.
- Contact fields according to authorization.
- Registration time.
- Check-in time.
- QR status.
- Meeting.
- Attendance status.

## 21.3 Redemption Report

Include:

- Participant.
- Meeting.
- Meal session.
- Entitlement type.
- Redemption time.
- Scanner operator.
- Device.
- Result.
- Rejection reason.
- Override or reversal information.

## 21.4 Package Consumption Report

Include:

- Meeting.
- Package.
- Expected quantity.
- Registered participants.
- Redeemed quantity.
- Remaining quantity.
- Consumption percentage.

## 21.5 Room Utilization Report

Include:

- Meeting room.
- Date range.
- Total reserved hours.
- Total occupied hours.
- Utilization percentage.
- Cancellation rate.
- No-show rate.

Support export to:

```text
Excel
CSV
PDF
```

Use queued exports for large datasets.

Do not load an entire large dataset into application memory.

Apply authorization and hotel scope to all reports and exports.

---

# 22. UI and UX

Improve the user interface so it is professional, consistent, and practical for hotel operations.

Use consistent:

- Sidebar.
- Header.
- Breadcrumbs.
- Page titles.
- Filters.
- Data tables.
- Pagination.
- Empty states.
- Loading states.
- Error states.
- Confirmation dialogs.
- Toast notifications.
- Form validation feedback.
- Responsive behavior.

Main pages:

```text
Dashboard
Hotels
Meeting Rooms
Clients
Bookings
Meeting Schedule
Meeting Detail
Participants
Attendance
Meeting Packages
Meal Sessions
Scanner
Reports
Users
Roles and Permissions
Audit Logs
Settings
```

Recommended Meeting Detail tabs:

```text
Overview
Schedule
Participants
Attendance
Packages
Meal Sessions
Redemptions
QR Codes
Activity Log
```

The scanner page must be simple, fast, high-contrast, and optimized for operational use.

Do not replace the existing frontend technology unless there is a strong documented reason.

---

# 23. Timezone Handling

Default application timezone:

```env
APP_TIMEZONE=Asia/Jakarta
```

Each hotel must be able to define its own timezone.

Store important timestamps using PostgreSQL `timestamp with time zone`.

Convert display values to the hotel timezone.

Avoid mixing:

- Browser local time.
- PHP server time.
- Database server time.
- Hotel local time.

Create a consistent timezone service or helper.

Add tests for timezone conversion and cross-day meeting schedules.

---

# 24. Queue and Scheduler

Use queues for:

- Sending participant QR codes by email.
- Report exports.
- External booking synchronization.
- Document generation.
- Notifications.
- Integration retries.
- Heavy report processing.

Use the scheduler for:

- Automatically opening check-in.
- Marking eligible meetings as no-show.
- Closing expired meal sessions.
- Expiring QR credentials.
- Cleaning expired idempotency records.
- Detecting delayed meetings.
- Sending reminders.
- Monitoring failed integrations.
- Monitoring failed jobs.

All jobs must be:

- Idempotent.
- Retry-safe.
- Configured with timeout.
- Configured with backoff.
- Logged on failure.
- Observable.

Add failed-job monitoring and operational documentation.

---

# 25. Logging and Monitoring

Use structured logging.

Every request must have a `request_id`.

Log fields should include when relevant:

- Request ID.
- User ID.
- Hotel ID.
- Route.
- HTTP method.
- Response status.
- Request duration.
- Error code.
- Meeting ID.
- Participant ID.
- Meal-session ID.
- Device ID.

Never log raw QR tokens.

Add health endpoints:

```text
GET /health/live
GET /health/ready
```

The readiness endpoint should verify:

- Database connectivity.
- Cache connectivity.
- Queue or Redis connectivity when required.

Monitor:

- Failed jobs.
- Integration failures.
- High scan rejection rates.
- Duplicate scan attempts.
- Room conflicts.
- Slow requests.
- Slow queries.
- Queue backlog.
- Readiness failures.

---

# 26. Automated Testing

Use PostgreSQL for integration tests when PostgreSQL-specific behavior is involved. Do not rely only on SQLite for features such as exclusion constraints, range types, partial indexes, or locking behavior.

## 26.1 Unit Tests

At minimum:

- Package entitlement calculation.
- QR token generation and validation.
- Meeting state transitions.
- Room conflict calculation.
- Redemption eligibility.
- Remaining entitlement calculation.
- Entitlement synchronization.
- Duplicate participant detection.

## 26.2 Feature Tests

At minimum:

- Login.
- Authorization.
- Cross-hotel access prevention.
- Create meeting.
- Assign room.
- Detect room conflict.
- Update meeting without self-conflict.
- Register participant.
- Detect duplicate participant.
- Enforce quota.
- Generate participant QR.
- Regenerate participant QR.
- Revoke participant QR.
- Redeem coffee break.
- Redeem lunch.
- Redeem twice in the same session.
- Redeem without entitlement.
- Redeem in a closed session.
- Redeem with expired QR.
- Redeem with revoked QR.
- Redeem for a different meeting.
- Redeem across a different hotel.
- Idempotent scan retry.
- Override redemption.
- Reverse redemption.
- Cancel meeting.
- Complete meeting.
- Close remaining sessions.

## 26.3 Concurrency Test

Simulate two simultaneous redemption requests for the same participant and meal session.

Expected result:

```text
Exactly one redemption succeeds.
The other request returns ALREADY_REDEEMED or DUPLICATE_REQUEST.
```

The database must contain only one successful redemption for the participant-session pair.

Target at least 80% coverage for critical business logic.

Do not disable tests merely to make the pipeline pass.

---

# 27. Seeders and Demo Data

Create development seeders for:

- Super administrator.
- Hotel administrator.
- Scanner operator.
- Report viewer.
- One demo hotel.
- Several meeting rooms.
- Demo clients.
- Half Day package.
- Full Day package.
- A meeting scheduled for today.
- Demo participants.
- Meal sessions.
- Entitlements.

Do not use predictable credentials in production.

Document development credentials only in development-specific documentation or environment examples.

---

# 28. Docker and Deployment

Prepare:

```text
Dockerfile
docker-compose.yml
docker-compose.production.yml
nginx.conf
.env.example
```

Minimum services:

```text
app
nginx
postgres
redis
queue-worker
scheduler
```

Use:

- Multi-stage builds.
- Non-root containers where practical.
- Health checks.
- Persistent volumes.
- Graceful shutdown.
- Queue-worker restart policies.
- Log rotation.
- Secure environment injection.
- Production build optimization.

Do not run destructive migrations automatically in production without controlled deployment steps.

Create:

```text
docs/DEPLOYMENT.md
```

Include:

- Server requirements.
- PHP extensions.
- PostgreSQL requirements.
- Redis requirements.
- Environment variables.
- Build commands.
- Deployment commands.
- Migration steps.
- Seeder rules.
- Queue worker.
- Scheduler.
- Storage permissions.
- Reverse proxy.
- SSL.
- Backup.
- Restore.
- Rollback.
- Troubleshooting.

---

# 29. Backup and Disaster Recovery

Create:

```text
docs/BACKUP_AND_RECOVERY.md
```

Include:

- PostgreSQL backup.
- Backup schedule.
- Retention policy.
- Encrypted backup.
- Offsite backup.
- Point-in-time recovery when used.
- File-storage backup.
- Restore procedure.
- Restore testing.
- Disaster-recovery checklist.
- Recommended RPO.
- Recommended RTO.
- Credential and key backup considerations.

---

# 30. Documentation

Create or update:

```text
README.md
docs/ARCHITECTURE.md
docs/BUSINESS_FLOW.md
docs/DATABASE_SCHEMA.md
docs/API_DOCUMENTATION.md
docs/SECURITY.md
docs/DEPLOYMENT.md
docs/BACKUP_AND_RECOVERY.md
docs/MYSQL_TO_POSTGRESQL_MIGRATION.md
docs/TESTING.md
docs/OPERATIONS_MANUAL.md
docs/CODEBASE_AUDIT.md
```

Use Mermaid diagrams for:

- Booking flow.
- Attendance flow.
- Meeting QR flow.
- Participant QR flow.
- Redemption flow.
- Meeting lifecycle.
- Room lifecycle.
- Database relationships.
- Integration flow.
- Concurrency-safe redemption flow.

Documentation must reflect the actual final implementation, not only the intended design.

---

# 31. OpenAPI and API Client Collection

Document APIs using OpenAPI.

Include:

- Authentication.
- Authorization.
- Request formats.
- Response formats.
- Error codes.
- Pagination.
- Filtering.
- Sorting.
- Idempotency.
- Rate limits.
- Scanner responses.
- Integration APIs.
- Example payloads.

Also provide a Postman or Bruno collection.

---

# 32. Implementation Rules

Follow these rules:

1. Do not remove an existing feature until its replacement is implemented and verified.
2. Do not change an existing API contract without a compatibility plan.
3. Do not log raw QR tokens.
4. Do not place complex business logic in controllers.
5. Do not leave MySQL-specific queries after PostgreSQL migration.
6. Do not rely on frontend validation.
7. Do not process redemption outside a database transaction.
8. Do not use a generic scan-count rule instead of session entitlements.
9. Do not put participant IDs directly in QR payloads.
10. Do not execute destructive migrations without backup and rollback planning.
11. Do not ignore race conditions.
12. Do not create fake implementations or placeholders for core features.
13. Do not leave unresolved TODO items in the main business flow.
14. Do not disable tests to make the build pass.
15. Do not silently swallow exceptions.
16. Do not expose stack traces in production.
17. Do not hardcode credentials.
18. Do not rewrite the frontend without a documented need.
19. Do not trust tenant identifiers from the request.
20. Do not delete audit or redemption history to hide reversals.
21. Do not mark a phase complete while critical tests are failing.
22. Do not claim that a feature is complete unless it is implemented in the repository.

---

# 33. Implementation Phases

## Phase 1 — Audit and Stabilization

- Audit the repository.
- Run the application.
- Run existing tests.
- Identify broken flows.
- Fix critical runtime errors.
- Normalize environment configuration.
- Add baseline tests.
- Document the current state.
- Produce `docs/CODEBASE_AUDIT.md`.

## Phase 2 — PostgreSQL Migration

- Convert migrations.
- Convert raw SQL.
- Replace MySQL functions.
- Add PostgreSQL constraints.
- Create data migration scripts.
- Validate migrated data.
- Add rollback strategy.
- Run integration tests against PostgreSQL.

## Phase 3 — Core Domain Refactor

- Hotels.
- Meeting rooms.
- Clients.
- Bookings.
- Meeting events.
- Meeting packages.
- Participants.
- Attendance.
- State transitions.
- Tenant isolation.

## Phase 4 — QR and Redemption Engine

- Meeting QR.
- Participant QR.
- Meal sessions.
- Participant entitlements.
- Redemption.
- Duplicate prevention.
- Idempotency.
- Row locking.
- Concurrency tests.

## Phase 5 — Security and RBAC

- Authentication review.
- Roles.
- Permissions.
- Policies.
- Audit logs.
- Rate limiting.
- Security headers.
- Secret management.
- Cross-hotel tests.

## Phase 6 — Dashboard and Reporting

- Operational dashboard.
- Meeting report.
- Attendance report.
- Redemption report.
- Package consumption report.
- Room utilization report.
- Queued exports.

## Phase 7 — Integration and Automation

- External booking API.
- Integration authentication.
- Integration logs.
- Queues.
- Scheduler.
- Notifications.
- Retry handling.

## Phase 8 — Production Readiness

- Unit tests.
- Feature tests.
- Concurrency tests.
- Performance review.
- Docker.
- Deployment documentation.
- Backup and recovery.
- Monitoring.
- Final security review.

---

# 34. Required Output for Every Phase

For each phase:

1. Explain the findings.
2. List the files to create or modify.
3. Implement complete changes.
4. Show or summarize migrations.
5. Provide required commands.
6. Run tests.
7. Run formatting or linting.
8. Fix errors.
9. Update documentation.
10. Provide a completion checklist.

Use this report format:

```text
Phase:
Status:
Findings:
Changes:
Files changed:
Migrations:
Commands:
Testing:
Risks:
Notes:
Next step:
```

Do not only describe what should be done. Make the changes in the repository.

---

# 35. Main Acceptance Criteria

The project is considered complete only when:

- The application runs on PostgreSQL.
- Existing production data has a documented migration path.
- Room double-booking is prevented.
- Administrators can create and manage meetings.
- Administrators can assign rooms.
- Administrators can configure participant quota.
- Administrators can assign meeting packages.
- Participants can register through the meeting QR.
- Participants receive unique QR credentials.
- Scanner operators can validate and redeem participant QR codes.
- Coffee-break and lunch sessions are separate.
- A participant cannot redeem the same session twice.
- A participant can only redeem included entitlements.
- Concurrent scanning is safe.
- Retry requests are idempotent.
- All critical scans and status changes are auditable.
- Cross-hotel data access is prevented.
- API responses are consistent.
- Dashboard and reports are available.
- Queue workers and scheduler jobs operate correctly.
- Critical automated tests pass.
- Deployment documentation exists.
- Backup and recovery documentation exists.
- Docker deployment is available.
- No credentials are hardcoded.
- No unresolved critical security issues remain.
- No placeholder implementation remains in the core business flow.
- The project is usable by real hotel staff.

---

# 36. First Execution Instructions

Start by performing these actions in order:

1. Inspect the full repository structure.
2. Identify the exact Laravel, PHP, frontend, and dependency versions.
3. Inspect `.env.example`, configuration files, and deployment files.
4. Run the application using the documented setup.
5. Run all existing tests.
6. Audit all migrations and the current schema.
7. Search for all raw SQL and MySQL-specific syntax.
8. Locate all booking, room, meeting, participant, QR, attendance, package, meal-session, and scanner code.
9. Identify current API contracts and frontend dependencies.
10. Create `docs/CODEBASE_AUDIT.md`.
11. Produce a roadmap based on the actual codebase.
12. Fix critical issues in Phase 1 before proceeding.
13. Preserve backward compatibility wherever practical.
14. Do not rebuild the project blindly.
15. Continue phase by phase and validate each phase with tests.

When assumptions are necessary, document them clearly and prefer the least disruptive implementation.

Work from the actual repository and produce production-quality code, migrations, tests, documentation, and deployment assets.
