# Codebase Audit

**Phase:** Phase 1 - Audit and Stabilization  
**Audit date:** 2026-06-20  
**Scope:** Existing Laravel application only. No PostgreSQL migration, schema changes, new tenant model, scanner API, redemption engine, or reporting implementation were introduced.

## Executive Summary

The application is a Laravel 10 hotel meeting headcounter with Bootstrap 4/jQuery partial views, Laravel UI authentication, Spatie permissions, QR generation, and Yajra DataTables. The app boots and routes register successfully, but the pre-Phase 1 baseline had a failing default feature test, raw exception exposure in controllers, unrestricted Eloquent mass assignment, predictable meeting QR payloads, IP-based duplicate attendance checks, a broken client validation rule, empty edit/destroy controller methods, and unclosed Blade `@prepend` sections.

Phase 1 fixed the critical runtime/security issues that can be handled without schema changes. Several architectural and domain issues remain intentionally deferred because they require schema changes or later-phase modules.

## Technology Inventory

| Component | Observed State |
|---|---|
| PHP | Runtime 8.3.3; composer requirement ^8.1 |
| Laravel | 10.48.18; composer requirement ^10.10 |
| Database | Phase 1 default/documented MySQL; local `.env` observed by `php artisan about` reports `pgsql` |
| Frontend | Bootstrap 4, jQuery, Ample Admin assets, DataTables, Select2, Vite |
| Auth | Laravel UI auth, username-based login |
| API Auth | Laravel Sanctum installed; `/api/user` uses `auth:sanctum` |
| Permissions | Spatie Laravel Permission 6.9.0 installed |
| QR | SimpleSoftwareIO QR Code ~4 |
| DataTables | Yajra Laravel DataTables 10.0 |

## Application Architecture

| Area | Files / Directories | Notes |
|---|---|---|
| Controllers | `app/Http/Controllers`, `app/Http/Controllers/Module/*` | Business logic is still mostly controller-based |
| Models | `app/Models/Module/*`, `app/Models/Transaction/QRDetail.php` | Existing table names are explicit; fillable fields added in Phase 1 |
| Routes | `routes/web.php`, `routes/masterdata.php`, `routes/setting.php`, `routes/transaction.php`, `routes/api.php` | `report.php` exists but is empty and not registered |
| Views | `resources/views/module/*` | Partial views loaded by custom JS SPA routing |
| JS | `public/js/module/*`, `public/js/core/core.js` | jQuery/DataTables-based modules |
| Helpers | `app/Helpers/DataAccessHelpers.php` | Transaction number and formatting helpers |
| Config | `config/*` | MySQL default remains in config and `.env.example`; app timezone now environment-driven |

## Route Organization

Private application routes are grouped by module and protected with `auth` plus the existing `ajax` middleware. Phase 1 added existing permission checks to the private module groups.

| Prefix | Middleware / Permission | Status |
|---|---|---|
| `/` and `/home` | `auth`; `/home` also `ajax` | Working |
| `/master-data/client` | `auth`, `ajax`, `can:Client` | Stabilized |
| `/master-data/meeting-schedule` | `auth`, `ajax`, `can:Meeting Schedule` | Stabilized |
| `/setting/role` | `auth`, `ajax`, `can:Manage Role` | Stabilized |
| `/setting/permission` | `auth`, `ajax`, `can:Manage Permission` | Stabilized |
| `/transaction/meeting-attendance` private routes | `auth`, `ajax`, `can:Meeting Trans` | Stabilized |
| `/form-attendance` and attendance submit | `throttle:attendance` | Public by design, token/time validated |
| `/api/user` | `auth:sanctum` | Sanctum stateful middleware enabled |

## Inventory

| Module / File | Current Behavior | Phase 1 Status |
|---|---|---|
| `DashboardController` | Redirect/dashboard partials | Fixed `abort('404')` |
| `ClientController` | List, add, store, edit/update/destroy | Validation fixed; edit/update/destroy implemented |
| `MeetingScheduleController` | List, add, store, edit/update, QR generation, delete | Store/update wrapped; QR filenames randomized; safe errors |
| `MeetingAttendanceController` | Attendance list/form/submit | Invalid QR guard added; duplicate check no longer IP-based |
| `PermissionController` | List/add/store/edit/update/delete | Empty edit/update/destroy implemented; safe errors |
| `RoleControlller` | List/manage permissions | Existing typo in class name remains for compatibility |
| `PackageController` | Empty placeholder | Deferred; no active routes found |

## Model Inventory

| Model | Table | Phase 1 Status |
|---|---|---|
| `User` | `users` | Uses explicit fillable; factory fixed for username schema |
| `Client` | `m_client` | Explicit fillable added |
| `MeetingRooms` | `m_meeting_rooms` | Explicit fillable added |
| `MeetingSchedule` | `trx_meeting_schedule` | Explicit fillable; `room()`/`package()` aliases added while preserving `ruangan()`/`paket()` |
| `Package` | `m_packages` | Explicit fillable added |
| `RoomStatus` | `r_room_status` | Explicit fillable added |
| `MeetingAttendance` | `trx_meeting_attendance` | Explicit fillable; legacy `trx_metting_number` documented in relation |
| `QRDetail` | `qr_detail` | Explicit fillable added |

## Critical Findings Resolved

| Severity | Finding | Affected File | Resolution |
|---|---|---|---|
| High | Client validation rule separated `required` and `max:3` incorrectly | `ClientController::store` | Replaced with array validation rules |
| High | Raw exception objects returned in JSON | Multiple controllers | Added safe JSON error helper and removed `err_detail` payloads |
| Medium | `abort('404')` used string | `DashboardController::redirect` | Changed to `abort(404)` |
| High | `dd()` in helper catch blocks | `DataAccessHelpers` | Replaced with logging and safe fallback |
| Medium | Duplicate helper methods | `DataAccessHelpers` | `convertArrayToNumber()` now delegates to `convertToNumber()` |
| High | Multi-table meeting creation not atomic | `MeetingScheduleController::store` | Wrapped DB writes and QR file writes with rollback cleanup |
| High | QR filenames predictable/colliding | `MeetingScheduleController` | New filenames include slugged transaction plus random suffix |
| High | Newly generated QR only depended on predictable transaction number | `MeetingScheduleController` / `MeetingAttendanceController` | New QR URLs include a persisted random filename token that is validated |
| High | IP-based duplicate attendance | `MeetingAttendanceController::checkAttendance` | Replaced with hashed participant fingerprint from existing submitted fields |
| Medium | Empty client/permission edit or destroy methods | `ClientController`, `PermissionController` | Implemented bounded edit/update/delete behavior |
| Medium | Unclosed Blade `@prepend` caused risky tests | Module views | Added `@endprepend` to active partials |
| Medium | Sanctum stateful middleware disabled | `Http/Kernel.php` | Enabled for API group and documented env |
| Medium | Private route groups lacked permission checks | Module route files | Applied existing Spatie permissions safely |

## Deferred Findings

| Severity | Finding | Current Behavior | Risk | Target Phase |
|---|---|---|---|---|
| High | No multi-hotel tenant isolation | Single tenant/client-like records | Cross-hotel data exposure if multi-hotel is added naively | Phase 3/5 |
| High | No foreign keys on domain tables | Relationships are application-only | Orphan records and weak integrity | Phase 2/3 |
| High | Transaction number race condition | Count-based sequence remains | Duplicate transaction numbers under concurrency | Phase 2/3 |
| High | Final QR credential architecture missing | Raw public URL parameters still accepted for legacy compatibility | Legacy QR links remain forgeable if only old params are used | Phase 4 |
| High | Attendance identity model missing | Hash uses existing form fields only | Same person can bypass duplicate checks by changing fields | Phase 4/5 |
| Medium | No room conflict prevention | Room status toggled but no booking overlap checks | Double booking | Phase 3 |
| Medium | No meeting lifecycle state machine | No formal status transitions | Ambiguous operational state | Phase 3 |
| Medium | Reports route empty | No reporting module | Missing business reporting | Phase 6 |
| Medium | Dashboard placeholder | Hardcoded/demo dashboard | No operational visibility | Phase 6 |
| Medium | No audit trail | Critical changes are not audited | Weak accountability | Phase 5/8 |
| Medium | User management missing | Auth users exist; no management module | Manual user administration | Phase 5 |

## Security Risks

Resolved in Phase 1: raw exception responses, unrestricted `$guarded = []`, unauthenticated private route access, missing permission checks on module groups, unthrottled attendance form/submit, and predictable QR filenames for new QR codes.

Remaining risks: `APP_DEBUG` was enabled in the observed local environment; legacy QR URLs without `qr_token` are still accepted for backward compatibility; no hotel scoping exists; no audit trail exists; and the final QR token hash/revocation model requires Phase 4 schema work.

## Data Integrity Risks

Resolved in Phase 1: meeting schedule creation now uses a transaction and cleans up newly written QR files when the DB operation fails. Meeting update now fixes the room availability toggle by releasing the previous room only when the room changes and booking the selected room.

Remaining risks: no database foreign keys for many relationships, count-based transaction numbers, no unique indexes for business keys, no room overlap prevention, hard deletes for some records, and legacy typo `trx_metting_number`.

## MySQL / PostgreSQL Migration Risks

No PostgreSQL migration was implemented in Phase 1. Current migration risks include `renameColumn()` usage, string prices in `m_packages`, missing constraints/indexes, MySQL-oriented assumptions in local development, and Indonesian column names scheduled for later refactor. No application raw SQL requiring MySQL-only syntax was found in the targeted scan.

## Performance Risks

| Risk | Example | Status |
|---|---|---|
| N+1 lookups | `MeetingScheduleController::renderAction()` fetches client per row | Deferred |
| Eager loading plus full `get()` before DataTables | Schedule and attendance data methods | Deferred |
| Missing indexes | Schedule date/client, QR meeting, attendance transaction fields | Deferred to schema phases |
| Public QR form brute force | Public token route | Rate limit added; final opaque token deferred |

## Technical Debt

Controllers still contain significant business logic. `RoleControlller` has a class-name typo. Several table and column names are legacy Indonesian names. `trx_metting_number` is misspelled and preserved for compatibility. The custom SPA routing pattern remains and should be preserved unless a later documented decision replaces it.

## Test Coverage

Phase 1 added smoke tests for guest redirect, valid login, authenticated client list access, authenticated meeting schedule list access, valid attendance QR form access, invalid QR handling without 500, master-data authentication, and permission middleware denial.

Baseline before fixes: `php artisan test` failed because the default Laravel example expected `/` to return 200 but the app correctly redirects guests to login.

Final result: `php artisan test` passed with 9 tests and 14 assertions.

## Recommended Target Architecture

Keep the Bootstrap 4/jQuery frontend pattern while extracting business logic from controllers into domain Actions/Services. Add database constraints during the PostgreSQL migration, then refactor meeting, attendance, QR, redemption, reporting, and integration domains under a clear bounded-context structure. Enforce hotel scoping and RBAC at query and route boundaries. Store only hashed QR tokens after Phase 4.

## Eight-Phase Roadmap Summary

1. Audit and Stabilization - completed for applicable Phase 1 scope.
2. PostgreSQL Migration - migrate DB, add constraints/indexes, address sequence races.
3. Core Domain Refactor - hotel scoping, meeting lifecycle, rooms/bookings.
4. QR and Redemption - opaque QR credentials, entitlements, redemptions.
5. Security and RBAC - full user management, policies, audit trail.
6. Dashboard and Reporting - real operational dashboards and reports.
7. Integration and Automation - external integrations and automation.
8. Production Readiness - deployment, monitoring, backups, hardening.

## Readiness Assessment for Phase 2

Phase 1 is ready to proceed after review. The app boots, routes register, smoke tests pass, formatting passes, raw controller exception responses were removed, and models now have explicit fillable fields. Phase 2 should start with the documented database mismatch: `.env.example` remains MySQL as required for Phase 1, while the inspected local `.env` currently reports `pgsql`.
