# Architecture

## Phase 3 Domain Structure

The application keeps the existing Laravel, Bootstrap 4, jQuery, Ample Admin, and `core.js` partial-view flow. Phase 3 adds canonical domain modules beside legacy compatibility code.

```text
app/
  Domain/
    Hotel/
    Booking/
    Meeting/
    Catering/
    Participant/
    Attendance/
  Actions/
  Services/
  Policies/
  Support/Tenancy/
```

## Domain Modules

```mermaid
flowchart LR
    Hotel[Hotel Domain] --> Booking[Booking Domain]
    Hotel --> Meeting[Meeting Domain]
    Hotel --> Catering[Catering Domain]
    Hotel --> Participant[Participant Domain]
    Booking --> Meeting
    Meeting --> Participant
    Meeting --> Attendance[Attendance Domain]
    Catering --> Assignment[Package Assignments]
    Assignment --> Meeting
```

## Tenant Resolution

Normal users are scoped to `users.hotel_id`. Super admins use `tenant_hotel_id` in the session when they intentionally switch context. Without a selected tenant, super admins can view all tenant data.

```mermaid
flowchart TD
    Request[HTTP request] --> Auth{Authenticated?}
    Auth -->|No| Continue[Continue without tenant]
    Auth -->|Yes| Super{Super Admin?}
    Super -->|No| UserHotel[Set context from user.hotel_id]
    Super -->|Yes| Session{tenant_hotel_id in session?}
    Session -->|Yes| Selected[Set selected active hotel]
    Session -->|No| All[No hotel filter for platform view]
    UserHotel --> Scope[ScopeByHotel filters queries]
    Selected --> Scope
    All --> Scope
```

## Super-Admin Switching

```mermaid
flowchart LR
    Navbar[Navbar selector] --> Validate[Validate active hotel]
    Validate --> Session[Store tenant_hotel_id]
    Session --> Middleware[SetTenantScope]
    Middleware --> Scoped[Hotel-scoped screens use selected tenant]
    Navbar --> Reset[Reset context]
    Reset --> Platform[Platform all-hotels view]
```

## Compatibility Layer

Legacy tables and routes remain for backward compatibility:

- `m_client`
- `m_meeting_rooms`
- `m_packages`
- `trx_meeting_schedule`
- `trx_meeting_attendance`

Canonical Phase 3 routes use domain models and canonical attributes. Legacy routes are retained and documented for gradual removal after canonical UI adoption is stable.

## Phase 4 QR And Redemption

Phase 4 adds `App\Domain\QRCode`, `App\Domain\Redemption`, and catering `MealSession` workflows. Controllers delegate token lifecycle, entitlement generation, idempotency, and redemption mutation to services/actions. Scanner mutation uses PostgreSQL row locks and a partial unique redemption index.

Final Phase 4 remediation adds safe persisted rejected attempts, append-only override records linked by `redemptions.original_redemption_id`, a browser camera scanner module using `html5-qrcode`, and `ParticipantQRCodeController` for operational participant QR lifecycle management. The true concurrency test uses two independent Artisan worker processes and PostgreSQL locks rather than sequential duplicate checks.
