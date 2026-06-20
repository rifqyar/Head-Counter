# Phase 3 — Core Domain Refactor

## Objective

Restructure the application from the current flat model structure to the target domain-driven architecture. Implement the core domain entities: Hotels, Meeting Rooms, Clients, Bookings, Meeting Events, Meeting Packages, Participants, and Attendance. Implement tenant isolation and state transitions.

**This phase establishes the domain model. QR and Redemption engine come in Phase 4.**

---

## Prerequisites

- Phase 1 complete: baseline stability
- Phase 2 complete: application runs on PostgreSQL

---

## Target Architecture

```
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

---

## Tasks

### 3.1 Hotels Domain

Create the `hotels` table and model:

- Migration: `hotels` (id, code, name, address, timezone, status, settings JSONB, timestamps)
- Model: `App\Domain\Hotel\Hotel`
- Enum: `HotelStatus` (ACTIVE, INACTIVE)
- Seeder: One demo hotel
- Policy: `HotelPolicy` (view, manage)
- Controller: `HotelController` (CRUD)
- Views: Hotel list, create, edit

### 3.2 Multi-Hotel Tenant Isolation

- Add `hotel_id` to `users` table
- Create `SetTenantScope` middleware that resolves the current hotel from the authenticated user
- Create `ScopeByHotel` trait for models that belong to a hotel
- Apply tenant scoping to all hotel-scoped queries
- Write tests: Hotel A user cannot access Hotel B data
- A super-admin can switch hotel context

### 3.3 Meeting Rooms Domain

Refactor `m_meeting_rooms` → `meeting_rooms`:

- Migration: `meeting_rooms` (id, hotel_id, code, name, floor, capacity, operational_status, facilities JSONB, timestamps)
- Model: `App\Domain\Meeting\MeetingRoom`
- Enum: `RoomOperationalStatus` (AVAILABLE, RESERVED, OCCUPIED, CLEANING, MAINTENANCE, INACTIVE)
- Replace `RoomStatusEnum` with new enum
- Drop `r_room_status` reference table (replace with enum)
- Controller: `MeetingRoomController`
- Views: Room list, create, edit, detail

### 3.4 Clients Domain

Refactor `m_client` → `clients`:

- Migration: `clients` (id, hotel_id, external_id, company_name, contact_name, contact_email, contact_phone, billing_address, tax_number, metadata JSONB, timestamps)
- Model: `App\Domain\Booking\Client`
- Migrate existing `m_client` data
- Controller: `ClientController` (rewrite with proper validation)
- Form Request: `StoreClientRequest`, `UpdateClientRequest`
- Views: Client list, create, edit, detail

### 3.5 Bookings Domain

Create new `bookings` table:

- Migration: `bookings` (id, hotel_id, external_booking_id, client_id, booking_number, booking_source, booking_date, status, notes, created_by, updated_by, timestamps)
- Enum: `BookingStatus` (DRAFT, CONFIRMED, CANCELLED, COMPLETED)
- Model: `App\Domain\Booking\Booking`
- Unique constraint: `hotel_id + booking_source + external_booking_id`
- Controller: `BookingController`
- Views: Booking list, create, edit, detail

### 3.6 Meeting Events Domain

Refactor `trx_meeting_schedule` → `meeting_events`:

- Migration: `meeting_events` (id, hotel_id, booking_id, meeting_room_id, event_name, event_date, start_at, end_at, expected_participants, actual_participants, status, meeting_qr_token_hash, checkin_open_at, checkin_close_at, started_at, completed_at, cancelled_at, created_by, updated_by, timestamps)
- Enum: `MeetingStatus` (DRAFT, SCHEDULED, CHECKIN_OPEN, OCCUPIED, COMPLETED, CANCELLED, NO_SHOW)
- Model: `App\Domain\Meeting\MeetingEvent`
- State machine: `MeetingStateTransition` service with explicit allowed transitions
- Controller: `MeetingEventController`
- Views: Event list, create, edit, detail (with tabs)

### 3.7 Meeting Room Conflict Prevention

- Create `MeetingRoomConflictService` that checks: `existing.start_at < requested.end_at AND existing.end_at > requested.start_at`
- Exclude inactive statuses (CANCELLED, NO_SHOW)
- Application-level validation in Form Request
- Database-level: PostgreSQL exclusion constraint using `btree_gist` and `tstzrange`
- Write tests for: exact overlap, partial overlap, contained overlap, adjacent meetings, cancelled meeting, editing same meeting

### 3.8 Meeting Packages Domain

Refactor `m_packages` → `meeting_packages`:

- Migration: `meeting_packages` (id, hotel_id, code, name, description, price NUMERIC, is_active, metadata JSONB, timestamps)
- Model: `App\Domain\Catering\MeetingPackage`
- Migrate existing package data (convert `price` from string to decimal, `count_qr` → separate entitlements)
- Controller: `MeetingPackageController`
- Views: Package list, create, edit, detail

### 3.9 Package Entitlements

Create `package_entitlements` table:

- Migration: `package_entitlements` (id, package_id, entitlement_type, quantity, metadata JSONB, timestamps)
- Enum: `EntitlementType` (COFFEE_BREAK, LUNCH, DINNER, SNACK, WELCOME_DRINK, CUSTOM)
- Model: `App\Domain\Catering\PackageEntitlement`
- Migrate `m_packages.count_qr` → entitlement records

### 3.10 Meeting Package Assignments

Create `meeting_package_assignments` table:

- Migration: `meeting_package_assignments` (id, meeting_event_id, package_id, participant_quota, unit_price, notes, timestamps)
- Model: `App\Domain\Catering\MeetingPackageAssignment`

### 3.11 Participants Domain

Refactor `trx_meeting_attendance` → `participants` (split attendance from participant):

- Migration: `participants` (id, hotel_id, meeting_event_id, participant_number, full_name, company_name, email, phone, identity_reference, registration_source, status, registered_at, checked_in_at, metadata JSONB, timestamps)
- Enum: `ParticipantStatus` (REGISTERED, CHECKED_IN, CANCELLED, BLOCKED)
- Model: `App\Domain\Participant\Participant`
- Duplicate detection: normalized email + meeting_event_id, or normalized phone + meeting_event_id, or identity_reference + meeting_event_id
- Controller: `ParticipantController`

### 3.12 Meeting Attendance Domain (separate from Participants)

Create `meeting_attendances` table:

- Migration: `meeting_attendances` (id, meeting_event_id, participant_id, attendance_type, attended_at, verification_method, device_id, scanned_by, metadata JSONB, created_at)
- Enum: `AttendanceType` (MEETING_CHECKIN, MEETING_CHECKOUT)
- Model: `App\Domain\Attendance\MeetingAttendance`
- Prevent duplicate check-in records

### 3.13 Refactor Actions/Services

Move business logic from controllers into Action classes:

- `CreateMeetingEventAction`
- `UpdateMeetingEventAction`
- `AssignRoomAction`
- `CheckRoomConflictAction`
- `TransitionMeetingStatusAction`
- `RegisterParticipantAction`
- `CreateClientAction`

Each Action should use Form Requests for validation and return structured results.

### 3.14 Update Routes

Reorganize routes:

```
/web:
  /                    → Dashboard
  /hotels/*            → HotelController
  /meeting-rooms/*     → MeetingRoomController
  /clients/*           → ClientController
  /bookings/*          → BookingController
  /meetings/*          → MeetingEventController
  /participants/*      → ParticipantController
  /packages/*          → MeetingPackageController

/api/v1:
  /user                → Authenticated user
  /meetings/*          → Meeting API endpoints
  /participants/*      → Participant API endpoints
```

Deactivate old routes gradually; maintain backward compatibility where needed.

### 3.15 Update Blade Views

- Keep Bootstrap 4 / Ample Admin theme (do not rewrite frontend)
- Update all views to use new model attributes (renamed columns)
- Update JavaScript DataTable columns to match new field names
- Ensure all AJAX endpoints match new route names

### 3.16 Write Feature Tests

| Test | Description |
|------|-------------|
| Hotel CRUD | Create, read, update, delete hotels |
| Room CRUD | Create, read, update, delete rooms |
| Client CRUD | Create, read, update, delete clients |
| Meeting CRUD | Create, read, update, delete meetings |
| Room conflict | Detect and prevent overlapping meetings |
| Meeting lifecycle | Transition states correctly |
| Cross-hotel isolation | Hotel A cannot see Hotel B data |
| Participant registration | Register participant with duplicate detection |

---

## Completion Checklist

- [ ] New domain directories created under `app/Domain/`
- [ ] All migrations run cleanly (`php artisan migrate:fresh`)
- [ ] Old migrations preserved (or renamed-column migrations applied)
- [ ] Action classes extracted from controllers
- [ ] Form Requests for all input endpoints
- [ ] Policies for all protected resources
- [ ] Tenant isolation middleware working
- [ ] Room conflict detection working
- [ ] Meeting state transitions enforced
- [ ] All existing Blade views updated to new field names
- [ ] Feature tests pass
- [ ] Code formatted

---

## Exit Criteria

Before starting Phase 4:

1. Application runs on PostgreSQL with new domain models
2. Room conflicts are prevented at application and database level
3. Meeting state transitions are controlled
4. Tenant isolation tests pass
5. All CRUD operations work through the UI
6. Old routes redirect or maintain backward compatibility