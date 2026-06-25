# Current Status

**Last Updated:** 2026-06-22

## Phase Progress

| Phase | Status | Start Date | Completion Date |
|-------|--------|------------|-----------------|
| Phase 1 - Audit and Stabilization | COMPLETED | 2026-06-20 | 2026-06-20 |
| Phase 2 - PostgreSQL Migration | COMPLETED | 2026-06-20 | 2026-06-20 |
| Phase 3 - Core Domain Refactor | COMPLETED | 2026-06-20 | 2026-06-20 |
| Phase 4 - QR and Redemption Engine | COMPLETED | 2026-06-20 | 2026-06-20 |
| Phase 5 - Security and RBAC | COMPLETED | 2026-06-20 | 2026-06-21 |
| Phase 6 - Dashboard and Reporting | COMPLETED | 2026-06-21 | 2026-06-21 |
| Phase 7 - Integration and Automation | Not Started | - | - |
| Phase 8 - Production Readiness | Not Started | - | - |

## Technology Stack

| Component | Version |
|-----------|---------|
| Laravel | 10.48.18 |
| PHP | ^8.1; local runtime 8.3.3 |
| Database | Existing active database verified as PostgreSQL `head_counter` |
| Frontend | Bootstrap 4, jQuery, Ample Admin, DataTables, Select2, SweetAlert2 |
| Auth | Laravel UI + Sanctum ^3.3 |
| Permissions | Spatie Laravel Permission 6.9.0 |
| QR | SimpleSoftwareIO QR Code ~4 |
| DataTables | Yajra Laravel DataTables 10.0 |
| Build | Vite ^4.0 |

## Phase 1 Execution Status

**Current Phase:** Phase 1 - Audit and Stabilization  
**Current Status:** COMPLETED

### Completed Work

- COMPLETED: Produced `docs/CODEBASE_AUDIT.md` from the actual repository.
- COMPLETED: Fixed the client validation rule bug.
- COMPLETED: Removed raw exception JSON exposure from stabilized controller paths.
- COMPLETED: Replaced `abort('404')` with `abort(404)`.
- COMPLETED: Removed `dd()` from `DataAccessHelpers`.
- COMPLETED: Consolidated duplicate number-conversion helpers.
- COMPLETED: Wrapped meeting schedule creation/update in transactions with QR file cleanup.
- COMPLETED: Fixed room availability toggling on meeting update.
- COMPLETED: Added randomized QR filenames and token validation for newly generated meeting QR links.
- COMPLETED: Replaced IP-based duplicate attendance checks with a hash of existing participant fields.
- COMPLETED: Added explicit `$fillable` fields to application models.
- COMPLETED: Added conventional `room()` and `package()` relationship names while preserving legacy aliases.
- COMPLETED: Enabled Sanctum stateful API middleware.
- COMPLETED: Added authentication, existing permission middleware, and attendance throttling.
- COMPLETED: Updated `.env.example`.
- COMPLETED: Added baseline Phase 1 smoke tests.
- COMPLETED: Closed active Blade `@prepend` stacks that caused risky tests.

### Partially Completed Work

- PARTIALLY COMPLETED: QR security was improved for newly generated QR codes without schema changes. Full opaque hashed QR credentials are deferred to Phase 4.
- PARTIALLY COMPLETED: Duplicate attendance detection no longer uses IP, but remains limited by the lack of a participant identity model.
- PARTIALLY COMPLETED: Authorization now uses existing permissions on module groups; full user-management/policy design remains deferred.

### Blocked Work

- BLOCKED: No Phase 1 blocker remains.

### Deferred Work

- DEFERRED: PostgreSQL migration, foreign keys, indexes, and sequence-safe transaction numbering to Phase 2.
- DEFERRED: Multi-hotel tenant isolation, meeting lifecycle, and room conflict prevention to Phase 3.
- DEFERRED: Final QR credential architecture, entitlements, scanner API, and redemption to Phase 4.
- DEFERRED: Full RBAC/user management and audit trail to Phase 5.
- DEFERRED: Dashboard analytics and reports to Phase 6.

### Files Changed

- `.env.example`
- `app/Helpers/DataAccessHelpers.php`
- `app/Http/Controllers/Controller.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/Module/MasterData/ClientController.php`
- `app/Http/Controllers/Module/MasterData/MeetingScheduleController.php`
- `app/Http/Controllers/Module/Setting/PermissionController.php`
- `app/Http/Controllers/Module/Transaction/MeetingAttendanceController.php`
- `app/Http/Kernel.php`
- `app/Models/*`
- `app/Providers/RouteServiceProvider.php`
- `config/app.php`
- `database/factories/UserFactory.php`
- `docs/CODEBASE_AUDIT.md`
- `docs/progress/CURRENT_STATUS.md`
- `docs/progress/DECISIONS.md`
- `public/js/module/masterdata/client.js`
- `public/js/module/setting/permission.js`
- `resources/views/errors/403.blade.php`
- `resources/views/module/*`
- `routes/masterdata.php`
- `routes/setting.php`
- `routes/transaction.php`
- `routes/web.php`
- `tests/Feature/PhaseOneSmokeTest.php`

### Tests Executed

| Command | Result |
|---|---|
| `php artisan about` | Exit 0; app boots; local DB reported as `pgsql` |
| `php artisan route:list` | Exit 0; routes register |
| `php artisan optimize:clear` | Exit 0 |
| `php artisan test` before fixes | Exit 1; default example expected `/` 200 but app redirects guests |
| `php artisan test` after fixes | Exit 0; 9 tests passed, 14 assertions |
| `./vendor/bin/pint` | Exit 0 after formatting |

### Known Risks

- Earlier project documentation incorrectly treated the existing database as MySQL. Re-audit confirmed the active database is PostgreSQL `head_counter`.
- Legacy QR URLs without the new `qr_token` remain accepted for backward compatibility.
- Transaction numbering is still count-based and race-prone until schema/sequence work.
- No hotel scoping, audit trail, report module, redemption engine, or scanner API exists yet.

## Next Step

Phase 6 is complete. Recommended next step: start Phase 7 only after review.

## Tenant Settings, User, Hotel, and Subscription Administration

**Current Status:** COMPLETED

### Completed Work

- COMPLETED: Added a tenant settings screen where hotel admins can maintain their own hotel profile, logo path, contact details, QR note, default booking source, address, and timezone.
- COMPLETED: Added super-admin subscription administration backed by `hotels.settings.subscription`, with plan, status, start date, expiry date, user limit, and notes.
- COMPLETED: Fixed the Setting sidebar so Manage User opens the existing tenant-scoped `/users` manager instead of a placeholder link.
- COMPLETED: Added Setting sidebar links for Hotel Settings, Subscriptions, and Tenant Switch where permitted.
- COMPLETED: Added a super-admin tenant selector to the Users screen so platform admins can manage users for a specific hotel.
- COMPLETED: Exposed subscription and tenant user quick actions from hotel detail.
- COMPLETED: Added audit logging for tenant settings and subscription changes.
- COMPLETED: Added focused regression coverage for tenant settings updates, super-admin subscription updates, and tenant user filtering.
- COMPLETED: Enhanced Manage Role and Manage Permission screens with the canonical admin shell, summary counters, cleaner DataTables, Bootstrap 4 tooltips, and improved create/edit forms.
- COMPLETED: Reworked the role permission editor into grouped permission sections with Select All and Clear controls.
- COMPLETED: Reworked the subscription page so hotel list and subscription status appear first, with inline expandable edit forms.
- COMPLETED: Fixed missing sidebar icons for Subscriptions and All Reports by using loaded Font Awesome icons.
- COMPLETED: Improved select option rendering by removing an invalid Select2 theme URL, adding local Select2 styling, initializing Select2 after AJAX page renders, and applying Select2 to role, tenant, status, and API ability selects.
- COMPLETED: Fixed multiple-select form serialization so selected values are submitted as repeated array values instead of one comma-separated string.
- COMPLETED: Fixed seeded General Manager and Hotel Admin roles so both receive the legacy `Setting` and `Manage User` menu permissions as well as `settings.manage` and `user.manage`.
- COMPLETED: Re-seeded the local database and cleared caches so existing `oria.admin` and `oria.gm` accounts show Setting, Manage User, and Hotel Settings immediately.
- COMPLETED: Fixed Role and Permission DataTables in AJAX-loaded pages by returning their script tags directly in the partial response instead of using layout-only Blade stacks.
- COMPLETED: Fixed meeting completion by allowing managers to complete meetings directly from `SCHEDULED` or `CHECKIN_OPEN`, while setting missing started/check-in timestamps safely.
- COMPLETED: Limited meeting transition dropdowns to valid next statuses so users are not offered backend-rejected transitions.
- COMPLETED: Added regression coverage for hotel admin/GM Setting menu visibility, direct scheduled-to-completed meeting transitions, and Role/Permission DataTable JSON data.

### Tests And Validation Executed

| Command | Result |
|---|---|
| `php artisan route:list --path=settings` | Exit 0; settings and subscription routes registered |
| `php artisan route:list --path=users` | Exit 0; user management routes registered |
| `php artisan route:list --path=setting` | Exit 0; role, permission, settings, and subscription routes registered |
| `php artisan db:seed --force` | Exit 0; local seeded hotel roles refreshed |
| `php artisan optimize:clear` | Exit 0; caches cleared after local reseed |
| `php artisan test tests\\Feature\\PhaseFiveCompletionTest.php --filter="settings\|subscription\|user management"` | Exit 0; 2 tests passed, 14 assertions |
| `php artisan test tests\\Feature\\PhaseFiveCompletionTest.php --stop-on-failure` | Exit 0; 9 tests passed, 87 assertions |
| `php artisan test` | Exit 0; 72 tests passed, 499 assertions |
| `./vendor/bin/pint` | Exit 0; formatting applied |
| `./vendor/bin/pint --test` | Exit 0; 285 files passed |

### Known Risks

- Subscription data is intentionally stored in `hotels.settings` JSON for this bounded administration pass. A dedicated billing/subscription table may be needed later if invoices, payment history, package limits, or subscription lifecycle automation become requirements.
- Logo settings currently accept a public asset path; file upload/storage management remains a future enhancement.

## Booking, Package, QR, and GM Menu Enhancement

**Current Status:** COMPLETED

### Completed Work

- COMPLETED: Enhanced canonical booking create/edit to support a step-style booking flow with package selection, meeting room assignment, meeting schedule, expected participant quota, booking source select options, and auto-generated booking numbers when blank.
- COMPLETED: Fixed booking wizard rendering by adding a booking-specific jQuery Steps initializer, hiding fallback submit buttons after wizard initialization, and keeping a cancel action inside the wizard controls.
- COMPLETED: Moved the booking wizard initializer into the AJAX-returned booking partial so the stepped form works when loaded through the app's `core.js` partial renderer.
- COMPLETED: Booking creation now transactionally creates the primary meeting event and package assignment when meeting details are supplied, while preserving legacy booking-only submissions.
- COMPLETED: Added booking list/detail quick actions to confirm or cancel without opening edit; confirming issues the secure meeting QR through the existing hashed-token `MeetingQRService`, and cancelling revokes active meeting QR tokens.
- COMPLETED: Changed generated meeting attendance QR output from standalone SVG/PNG files to branded PDF tickets with hotel logo, hotel, client, schedule, room, booking, participant, and validity details as applicable.
- COMPLETED: Added participant QR ticket information and one-time PDF download output for public self-registration and participant QR administration while preserving hash-only token storage.
- COMPLETED: Added tenant-aware hotel logo resolution so authenticated pages and QR PDFs show the active hotel logo, with `public/images/logo-full.png` as the default fallback.
- COMPLETED: Fixed authenticated navbar logo rendering by removing Blade `@php` blocks from the header and clearing compiled views.
- COMPLETED: Rebuilt the public meeting participant registration page as a local Bootstrap/jQuery Steps wizard with hotel logo, meeting summary, identity step, confirmation step, validation feedback, and non-JavaScript fallback submit.
- COMPLETED: Added the missing participant QR scan flow for lunch, coffee break, and package entitlement sessions: successful redemption now changes a `REGISTERED` participant to `CHECKED_IN` in the same transaction, while duplicate scans for the same participant/session remain rejected.
- COMPLETED: Clarified the scanner UI as a participant entitlement scanner and expanded scan feedback with participant status, session, remaining entitlement, and redemption number.
- COMPLETED: Exposed the QR Scanner screen in the Transaction sidebar for users with `redemption.scan`, including scanner-only users who do not have the broader Transaction permission.
- COMPLETED: Added participant list filters by meeting, client, and meeting date, with client/date columns and query-string-preserving pagination.
- COMPLETED: Fixed scanner session visibility by auto-generating open entitlement sessions for package-backed meetings that do not yet have meal sessions, so every booking/meeting with package entitlements can appear in the scanner.
- COMPLETED: Fixed scanner camera startup for AJAX-rendered pages by loading the Vite scanner bundle from the app layout and exposing a reusable `HeadCounterScanner.init()` initializer called by the scanner partial.
- COMPLETED: Added participant QR reprint support after generate/rotate by storing a printable active QR PDF path on the credential and exposing a secure "Reprint QR PDF" download action.
- COMPLETED: Rebuilt the public participant QR issued page with the same local Bootstrap/styled card approach as the registration page, including hotel logo, QR frame, participant details, and PDF download action.
- COMPLETED: Changed meeting schedule creation to a booking-led process: canonical meeting create now selects one booking to process, legacy meeting-schedule add redirects to that booking-led flow, and booking-only processing reuses the existing primary meeting instead of duplicating schedule/package fields.
- COMPLETED: Reworked the legacy public meeting attendance form into a jQuery Steps wizard with company, participant, and confirmation steps.
- COMPLETED: Strengthened public participant registration from meeting QR by requiring at least email, phone, or identity reference so duplicate registration protections have a stable identity key.
- COMPLETED: Reworked package CRUD to support multiple entitlement rows per package while preserving legacy single-entitlement payload compatibility.
- COMPLETED: Restored report menu permissions for seeded General Manager and Hotel Admin roles so both can access meeting reports, all reports, and the export center.
- COMPLETED: Reworked the Meeting Attendance menu to open canonical `/meetings` operations for GM, Hotel Admin, and Front Office users instead of the legacy `transaction/meeting-attendance` list.
- COMPLETED: Added filters to the canonical meeting attendance operations list for meeting date, client, and status, and fixed the legacy attendance DataTable date filter to use the selected date without leaking rows through `orWhereDate`.
- COMPLETED: Limited booking-led meeting processing to unprocessed draft booking meetings so already scheduled attendance meetings do not keep appearing as selectable booking data.
- COMPLETED: Allowed Front Office and other attendance-authorized hotel users to edit meeting attendance operation data and transition meeting status such as `CHECKIN_OPEN`.
- COMPLETED: Fixed meeting attendance detail tabs by converting the meeting detail screen to Bootstrap tab panes with working Overview, Schedule, Participants, Attendance, and Packages sections.
- COMPLETED: Refreshed dashboard UI into a stronger operations view with a gradient context header, compact filter bar, KPI cards, attendance/redemption progress, room status pills, timeline meeting lists, and alert panels.
- COMPLETED: Added regression coverage for booking wizard creation plus QR issuance, booking wizard UI hooks, multi-entitlement packages, GM report access, and dashboard UI hooks.

### Tests And Validation Executed

| Command | Result |
|---|---|
| `php artisan test tests\\Feature\\PhaseThreeCompletionTest.php tests\\Feature\\PhaseFiveCompletionTest.php tests\\Feature\\PhaseFourQRRedemptionTest.php --stop-on-failure` | Exit 0; 23 tests passed, 156 assertions |
| `php artisan test` before formatting | Exit 0; 60 tests passed, 363 assertions |
| `./vendor/bin/pint` | Exit 0 |
| `php artisan test` final | Exit 0; 60 tests passed, 363 assertions |
| `./vendor/bin/pint --test` final | Exit 0 |
| `php artisan test` after wizard/dashboard UI pass | Exit 0; 60 tests passed, 369 assertions |
| `./vendor/bin/pint` after wizard/dashboard UI pass | Exit 0 |
| `php artisan test` after meeting/attendance wizard pass | Exit 0; 61 tests passed, 380 assertions |
| `./vendor/bin/pint --test` after meeting/attendance wizard pass | Exit 0 |
| `php artisan test tests\\Feature\\PhaseOneSmokeTest.php tests\\Feature\\PhaseThreeCompletionTest.php --stop-on-failure` after attendance operations pass | Exit 0; 20 tests passed, 117 assertions |
| `php artisan test` after attendance operations pass | Exit 0; 64 tests passed, 393 assertions |
| `./vendor/bin/pint` after attendance operations pass | Exit 0 |
| `php artisan test tests\\Feature\\PhaseThreeCompletionTest.php --stop-on-failure` after attendance detail tab fix | Exit 0; 10 tests passed, 101 assertions |
| `php artisan test tests\\Feature\\PhaseFourQRRedemptionTest.php --stop-on-failure` after QR PDF/logo pass | Exit 0; 10 tests passed, 68 assertions |
| `./vendor/bin/pint` after QR PDF/logo pass | Exit 0; 279 files checked, 1 style issue fixed |
| `./vendor/bin/pint --test` after QR PDF/logo pass | Exit 0; 279 files passed |
| `php artisan test` after QR PDF/logo pass | Exit 0; 64 tests passed, 403 assertions |
| `php artisan test tests\\Feature\\PhaseFourQRRedemptionTest.php tests\\Feature\\PhaseSixDashboardReportingTest.php --stop-on-failure` after logo/form fix | Exit 0; 16 tests passed, 103 assertions |
| `php artisan optimize:clear` after logo/form fix | Exit 0; compiled views and caches cleared |
| `./vendor/bin/pint` after logo/form fix | Exit 0; 279 files passed |
| `php artisan test` after logo/form fix | Exit 0; 65 tests passed, 414 assertions |
| `php artisan test tests\\Feature\\PhaseFourQRRedemptionTest.php --stop-on-failure` after participant QR scan flow fix | Exit 0; 10 tests passed, 79 assertions |
| `npm run test:scanner` after participant QR scan flow fix | Exit 0; scanner payload tests passed |
| `npm run build` after participant QR scan flow fix | Exit 0; Vite production build completed |
| `./vendor/bin/pint` after participant QR scan flow fix | Exit 0; 279 files passed |
| `php artisan test` after participant QR scan flow fix | Exit 0; 65 tests passed, 420 assertions |
| `php artisan test tests\\Feature\\PhaseThreeCompletionTest.php tests\\Feature\\PhaseFourQRRedemptionTest.php --stop-on-failure` after scanner menu and participant filters | Exit 0; 21 tests passed, 192 assertions |
| `./vendor/bin/pint` after scanner menu and participant filters | Exit 0; 279 files passed |
| `php artisan test` after scanner menu and participant filters | Exit 0; 66 tests passed, 432 assertions |
| `php artisan test tests\\Feature\\PhaseFourQRRedemptionTest.php --stop-on-failure` after scanner session/camera/reprint fix | Exit 0; 11 tests passed, 92 assertions |
| `npm run test:scanner` after scanner session/camera/reprint fix | Exit 0; scanner payload tests passed |
| `npm run build` after scanner session/camera/reprint fix | Exit 0; Vite production build completed |
| `php artisan migrate --force` after scanner session/camera/reprint fix | Exit 0; local database already had `participant_qr_credentials.printable_path` |
| `./vendor/bin/pint` after scanner session/camera/reprint fix | Exit 0; 280 files checked, 1 style issue fixed |
| `php artisan test` after scanner session/camera/reprint fix | Exit 0; 67 tests passed, 442 assertions |
| `php artisan test tests\\Feature\\PhaseFourQRRedemptionTest.php --stop-on-failure` after participant QR issued page fix | Exit 0; 11 tests passed, 94 assertions |
| `./vendor/bin/pint` after participant QR issued page fix | Exit 0; 280 files passed |
| `php artisan test` after participant QR issued page fix | Exit 0; 67 tests passed, 444 assertions |

### Known Risks

- Booking create/edit currently manages the primary meeting event for the booking; multi-meeting booking workflows remain available through the Meetings screen.
- Package entitlement edits replace package entitlement rows; participant entitlement synchronization remains the existing explicit Phase 4 action when historical participants already exist.

## Legacy Table Consolidation

**Current Status:** COMPLETED

### Completed Work

- COMPLETED: Added a PostgreSQL migration that copies same-purpose legacy data into canonical Phase 3 tables before removing duplicate physical legacy storage.
- COMPLETED: Replaced `m_meeting_rooms`, `m_client`, `m_packages`, `r_room_status`, `trx_meeting_schedule`, and `trx_meeting_attendance` physical tables with PostgreSQL compatibility views backed by `meeting_rooms`, `clients`, `meeting_packages`, `meeting_events`, `participants`, and `meeting_attendances`.
- COMPLETED: Added `INSTEAD OF` triggers so legacy Eloquent models and old QR/attendance flows can still write through the legacy names while canonical tables remain the source of truth.
- COMPLETED: Remapped legacy `qr_detail.meeting_id` values from old schedule IDs to canonical `meeting_events.id` before dropping the physical `trx_meeting_schedule` table.
- COMPLETED: Hardened legacy create migrations to drop leftover same-named PostgreSQL views before recreating old tables during `migrate:fresh`.
- COMPLETED: Updated `PhaseThreeDomainSeeder` to import only from physical legacy tables, avoiding feedback loops when legacy names are compatibility views.
- COMPLETED: Added regression coverage proving legacy names are PostgreSQL views and legacy model writes land in canonical tables.

### Tests And Validation Executed

| Command | Result |
|---|---|
| `php artisan migrate --force` | Exit 0; legacy table consolidation migration applied |
| `php artisan test tests\\Feature\\PostgresqlMigrationTest.php --stop-on-failure` | Exit 0; 5 tests passed, 6 assertions |
| `php artisan test tests\\Feature\\PhaseThreeCompletionTest.php --stop-on-failure` | Exit 0; 9 tests passed, 89 assertions |

### Known Risks

- Legacy relation names remain as compatibility views until old controllers and QR/attendance screens are fully retired.
- PostgreSQL view triggers preserve old write paths, but complex multi-hotel legacy writes still default to the Oria hotel context by design.

## Phase 6 Execution Status

**Current Phase:** Phase 6 - Dashboard and Reporting  
**Current Status:** COMPLETED

### Completed Work

- COMPLETED: Read `docs/phases/phase-6-dashboard-and-reporting.md` as the authoritative scope.
- COMPLETED: Verified Phase 5 baseline before implementation.
- COMPLETED: Replaced placeholder dashboard widgets with tenant-scoped operational metrics and alerts.
- COMPLETED: Added hotel-timezone date-boundary handling through `HotelTimezoneService`.
- COMPLETED: Added five report types: meetings, participant attendance, redemptions, package consumption, and room utilization.
- COMPLETED: Added filterable Bootstrap 4 report pages and an Export Center.
- COMPLETED: Added Excel, CSV, and PDF export support using maintained Laravel-compatible packages.
- COMPLETED: Added queued export tracking through `report_exports` and `ExportReportJob`.
- COMPLETED: Added secure authenticated export download handling.
- COMPLETED: Added scheduled expired export cleanup through `reports:cleanup-expired`.
- COMPLETED: Enforced `report.view`, `report.export`, tenant scope, requester ownership, and super-admin behavior.
- COMPLETED: Added performance indexes for report-heavy dashboard/report lookups.
- COMPLETED: Added focused Phase 6 tests for dashboard tenant scope, report permissions, report isolation, CSV export, secure download, cleanup, and formula escaping.
- COMPLETED: Added `docs/DASHBOARD_AND_REPORTING.md` and updated architecture, API, operations, schema, authorization, and decision docs.

### Tests And Validation Executed

| Command | Result |
|---|---|
| `php artisan optimize:clear` | Exit 0 |
| `php artisan migrate:status` | Exit 0; all migrations ran |
| `php artisan route:list --except-vendor` | Exit 0; 154 application routes |
| `./vendor/bin/pint --test` before edits | Exit 0 |
| `composer require maatwebsite/excel:"^3.1.55" barryvdh/laravel-dompdf:"^2.2"` | Exit 0 |
| `php artisan migrate --force` | Exit 0; Phase 6 migration applied |
| `php artisan test` after implementation | Exit 0; 58 tests passed, 344 assertions |
| `npm run build` | Exit 0 |
| `php artisan migrate:fresh --force` | Exit 0 |
| `php artisan db:seed --force` | Exit 0 |
| `php artisan migrate:rollback --force` | Exit 0; clean-batch rollback validated |
| `php artisan migrate --force` after rollback | Exit 0 |
| `php artisan db:seed --force` after rollback/migrate | Exit 0 |
| `php artisan queue:work --once --stop-when-empty` | Exit 0 |
| `php artisan schedule:run` | Exit 0; no due scheduled commands |
| `php artisan reports:cleanup-expired --dry-run` | Exit 0 |
| Excel/PDF generation smoke | Exit 0; generated non-empty XLSX/PDF bytes |
| Queued export smoke | Exit 0; export completed, progress 100, file exists |
| `php artisan test tests\Feature\PhaseSixDashboardReportingTest.php` final | Exit 0; 5 tests passed, 21 assertions |
| `./vendor/bin/pint --test` final | Exit 0 |

### Known Risks

- PDF export remains row-threshold constrained because DomPDF is memory intensive.
- Notification delivery for completed/failed exports is deferred; operators use the Export Center.
- Room utilization uses a documented 24-hour operating denominator until hotel business hours are configured.

## Phase 5 Execution Status

**Current Phase:** Phase 5 - Security and RBAC  
**Current Status:** COMPLETED

### Completed Work

- COMPLETED: Read `docs/phases/phase-5-security-and-rbac.md` as the authoritative scope.
- COMPLETED: Verified Phase 4 baseline documents and route/migration/test status before implementation.
- COMPLETED: Added idempotent `RolePermissionSeeder` with Phase 5 roles, canonical permissions, legacy compatibility permissions, and explicit role-permission matrix.
- COMPLETED: Updated super-admin detection for canonical `SUPER_ADMIN` while preserving legacy `Super Admin`.
- COMPLETED: Added and registered policies for meal sessions, redemptions, audit logs, users, reports, and integration API keys; tightened core domain policies to check permission plus hotel scope.
- COMPLETED: Added participant QR-specific policy authorization through `participant.qr.manage`.
- COMPLETED: Added tenant fail-closed behavior for inactive or missing hotel context.
- COMPLETED: Added global security headers middleware with CSP, nosniff, referrer policy, permissions policy, frame denial, and production-only HSTS.
- COMPLETED: Added scanner validate/redeem and sensitive-admin named rate limiters; attendance limiter now keys by token hash plus IP.
- COMPLETED: Extended audit logs with Phase 5 columns while preserving Phase 4 compatibility columns.
- COMPLETED: Hardened `AuditLogger` with recursive sensitive-field redaction and request metadata capture.
- COMPLETED: Added read-only tenant-scoped audit-log UI with filters and detail view.
- COMPLETED: Audited login success/failure/logout and tenant switching.
- COMPLETED: Restricted CORS to environment-driven origins and explicit methods/headers.
- COMPLETED: Added integration API key foundation with hash-only secret storage, prefix lookup, abilities, expiration, revocation, last-used tracking, and middleware alias.
- COMPLETED: Added focused Phase 5 tests for RBAC seeding, security headers, audit redaction/UI isolation, scanner authentication, integration keys, and login throttling.
- COMPLETED: Updated `docs/SECURITY.md`, `docs/AUTHORIZATION_MATRIX.md`, `docs/AUDIT_LOGGING.md`, and `docs/API_AUTHENTICATION.md`.
- COMPLETED: Added endpoint security matrix review in `docs/ENDPOINT_SECURITY_MATRIX.md` covering all 149 active application routes by endpoint family and controls.
- COMPLETED: Removed public registration and the unsafe `/test` phpinfo route.
- COMPLETED: Added global active-user enforcement plus `users.status`, `last_login_at`, `deactivated_at`, and `deactivated_by`.
- COMPLETED: Added tenant-scoped `/users` management workflows, role sync, user activation/deactivation, personal access token creation/revocation, protected-role filtering, and last-active-super-admin protection.
- COMPLETED: Added token ability enforcement for scanner API tokens with `scanner:validate` and `scanner:redeem`.
- COMPLETED: Converted remaining actionable legacy controller inline validation to Form Requests; remaining `Validator::make` is isolated to disabled public registration.
- COMPLETED: Added authoritative audit placement for booking create/update/cancel, user mutations, token mutations, role changes, and permission changes.
- COMPLETED: Added Phase 5 completion tests for mutation audit, user-management boundaries, protected super-admin behavior, role/permission audit, Sanctum token abilities/revocation, inactive user/hotel blocking, and cross-hotel scanner isolation.

### Deferred Work

- DEFERRED: HMAC request signing to Phase 7 integration work.
- DEFERRED: Full external integration endpoints to Phase 7.
- DEFERRED: Reporting screens and exports to Phase 6.
- DEFERRED: Any future public registration workflow must be redesigned with explicit tenant invitation controls before re-enabling.

### Blocked Work

- BLOCKED: No Phase 5 blocker remains.

### Tests And Validation Executed

| Command | Result |
|---|---|
| `php artisan optimize:clear` | Exit 0 |
| `php artisan migrate:status` | Exit 0 before Phase 5 edits |
| `php artisan route:list` | Exit 0; 143 routes after audit-log routes |
| `./vendor/bin/pint --test` before edits | Exit 0 |
| `php artisan migrate --force` | Exit 0; Phase 5 migration applied |
| `php artisan test tests\\Feature\\PhaseFiveSecurityTest.php` | Exit 0; 7 tests passed, 95 assertions |
| `php artisan test` after fixes | Exit 0; 47 tests passed, 289 assertions |
| `npm run build` | Exit 0; Vite build completed |
| `php artisan migrate:fresh --force` | Exit 0 |
| `php artisan db:seed --force` after fresh migration | Exit 0 |
| `php artisan migrate:rollback --force` | Exit 0 |
| `php artisan migrate --force` after rollback | Exit 0 |
| `php artisan db:seed --force` after rollback/migrate | Exit 0 |
| `./vendor/bin/pint` | Exit 0; 3 style issues fixed |
| `./vendor/bin/pint --test` | Exit 0 |
| `php artisan test` final | Exit 0; 53 tests passed, 323 assertions |
| `php artisan route:list --except-vendor` final completion pass | Exit 0; 149 application routes |
| `php artisan test tests\\Feature\\PhaseFiveCompletionTest.php` | Exit 0; 6 tests passed, 34 assertions |
| `php artisan test tests\\Feature\\PhaseFiveSecurityTest.php` | Exit 0; 7 tests passed, 95 assertions |

### Known Risks

- Legacy settings controllers remain Bootstrap/jQuery compatibility screens, but Phase 5 security controls, Form Requests for write inputs, protected-role checks, and audit logging are in place.
- HMAC signing is documented as deferred; current integration foundation is API-key based only.
- `SESSION_SECURE_COOKIE=false` remains the local `.env.example` default and must be true in HTTPS production.

## UI/UX and Domain Flow Remediation

**Current Status:** PARTIALLY COMPLETED

### Completed Work

- COMPLETED: Created `docs/UI_UX_REMEDIATION_AUDIT.md` covering active dashboard, domain, QR/redemption, tenant, and legacy settings flows.
- COMPLETED: Added shared canonical Blade partials for page headers, cards, validation summaries, form actions, and DataTables/Select2 behavior.
- COMPLETED: Standardized Hotels index, Meeting Rooms, Clients, Bookings, Meetings index, Packages index, Participants index, and Tenant Switcher with dashboard-style page titles, breadcrumbs, cards, action areas, table wrappers, badges, and empty states.
- COMPLETED: Fixed the Meeting Room sidebar icon by replacing the missing `mdi-door` class with loaded Font Awesome `fa fa-building`.
- COMPLETED: Made active hotel context visible in the main navbar for super-admin and normal hotel users.
- COMPLETED: Improved tenant switching to validate active hotels, keep previous context after invalid/inactive switch attempts, clear stale tenant filters, redirect to dashboard after success/reset, and show success feedback.
- COMPLETED: Added explicit meeting-room hotel selection for super-admins and tenant-derived hotel assignment for normal users.
- COMPLETED: Blocked meeting-room hotel reassignment when dependent meetings exist.
- COMPLETED: Added `client_hotel` many-to-many association table and backfilled from existing `clients.hotel_id` relationships.
- COMPLETED: Updated client model, client policy, client list/detail/forms, booking selectors, and booking validation to use active hotel associations.
- COMPLETED: Added regression tests for tenant-safe meeting-room assignment, shared client associations, hotel-scoped booking client selection, and failed tenant switch context preservation.
- COMPLETED: Fixed direct browser, form-redirect, and back-button navigation for canonical domain pages by routing non-AJAX GET requests through the existing `/redirect` full-layout wrapper while preserving AJAX partial responses for `core.js`.
- COMPLETED: Removed remaining full-layout wrappers from domain partials and added the shared page header/card pattern to Hotels, Meetings, Packages, Participants, Meal Sessions, Scanner, Participant QR Administration, and Redemptions secondary pages.
- COMPLETED: Updated architecture, business flow, database schema, operations manual, and decision records.

### Partially Completed Work

- PARTIALLY COMPLETED: Layout remediation is complete for the highest-traffic canonical domain pages; some legacy `module/*` pages and secondary canonical create/edit/detail pages still retain older structure for compatibility.
- PARTIALLY COMPLETED: Client-to-hotel association creation and non-destructive association syncing are implemented; explicit association removal UI is deferred until active-booking safety semantics are completed.
- PARTIALLY COMPLETED: DataTables remain client-side for canonical pages; server-side DataTables migration was not introduced in this remediation pass.
- PARTIALLY COMPLETED: Dashboard integration still contains legacy template/demo widgets and should be addressed in the dashboard/reporting phase.

### Blocked Work

- BLOCKED: No technical blocker remains for the implemented remediation scope.

### Tests And Validation Executed

| Command | Result |
|---|---|
| `php artisan optimize:clear` | Exit 0 |
| `php artisan route:list` | Exit 0; 141 routes registered |
| `php artisan test tests\\Feature\\PhaseThreeCompletionTest.php` | Exit 0; 7 tests passed, 51 assertions |
| `php artisan migrate:fresh --force` | Exit 0 |
| `php artisan db:seed --force` after fresh migration | Exit 0 |
| `php artisan test` before final formatting | Exit 0; 40 tests passed, 182 assertions |
| `./vendor/bin/pint` | Exit 0; 3 style issues fixed |
| `php artisan test` after Pint | Exit 0; 40 tests passed, 182 assertions |
| `npm install` | Exit 0; dependencies already up to date; npm audit reported 9 existing vulnerabilities |
| `npm run build` | Exit 0; Vite build completed |
| `php artisan migrate:rollback --force` | Exit 0 |
| `php artisan migrate --force` | Exit 0 |
| `php artisan db:seed --force` after rollback/migrate | Exit 0 |
| `php artisan test` after navigation wrapper fix | Exit 0; 40 tests passed, 194 assertions |
| `./vendor/bin/pint` after navigation wrapper fix | Exit 0 |
| `php artisan test tests\\Feature\\PhaseThreeCompletionTest.php tests\\Feature\\PhaseFourQRRedemptionTest.php` | Exit 0; 17 tests passed, 122 assertions |
| `php artisan test` after final header-layout sweep | Exit 0; 40 tests passed, 194 assertions |
| `./vendor/bin/pint` after final header-layout sweep | Exit 0 |

### Known Risks

- Legacy settings and legacy attendance screens still use older Ample partial conventions and are intentionally preserved.
- `clients.hotel_id` remains as a transitional compatibility field; future deprecation requires a planned migration after all references are association-aware.
- Association removal is not exposed yet because bookings and historical meetings need safe behavior before detaching a hotel from a client.
- Dashboard metrics still need a separate tenant-scoped operational pass before Phase 6.

## Phase 4 Execution Status

**Current Phase:** Phase 4 - QR and Redemption Engine  
**Current Status:** COMPLETED

### Completed Work

- COMPLETED: Added hash-only meeting QR lifecycle service with generation, validation, revocation, regeneration, last-four tracking, expiration, check-in window checks, and printable SVG storage.
- COMPLETED: Added public meeting QR registration routes.
- COMPLETED: Extended participant registration to create check-in attendance, participant entitlements, and active participant QR credentials inside transactions.
- COMPLETED: Added participant QR credential table, model, enum, and service with hash-only generation, rotation-by-replacement, revocation, and validation.
- COMPLETED: Added meal sessions table/model/service and Bootstrap 4 administration pages for list, create, edit, open, close, and cancel.
- COMPLETED: Added participant entitlement table/model/service with aggregate package entitlement generation and PostgreSQL quantity consistency check.
- COMPLETED: Added entitlement synchronization action that preserves redeemed quantities.
- COMPLETED: Added redemptions table/model/enums with PostgreSQL partial unique index for active successful participant-session redemption.
- COMPLETED: Added scanner validation and redemption API endpoints with Sanctum auth, tenant middleware, `redemption.scan`, request validation, idempotency cache, row locking, duplicate checks, and standardized JSON responses.
- COMPLETED: Added scanner UI with manual token fallback, meal-session selector, success/failure/warning colors, disabled pending state, and vibration feedback where supported.
- COMPLETED: Added reversal action and admin route that restores entitlement counters while preserving redemption history.
- COMPLETED: Added override action and admin route with non-overrideable tenant/identity boundary decisions.
- COMPLETED: Added Phase 4 permissions to seeders.
- COMPLETED: Added audit log table and audit records for QR, registration, entitlements, meal sessions, redemption success/reject, idempotency conflict, override, and reversal.
- COMPLETED: Added development seed data for meal sessions, participant entitlements, active participant QR credentials, scanner-capable roles, and override/reversal-capable administrators.
- COMPLETED: Added `scanner:idempotency-cleanup` and scheduled daily cleanup.
- COMPLETED: Added `docs/QR_AND_REDEMPTION.md`, `docs/API_DOCUMENTATION.md`, `docs/SECURITY.md`, and `docs/OPERATIONS_MANUAL.md`; updated architecture, business flow, and schema docs.
- COMPLETED: Added focused Phase 4 feature tests covering QR lifecycle, registration issuance, scanner validation, idempotency, duplicate prevention, cross-hotel rejection, reversal, and the partial unique index.
- COMPLETED: Added a true PostgreSQL concurrency test using two separate PHP worker processes, a filesystem synchronization barrier, row locks, and the partial unique success index.
- COMPLETED: Persisted safe operational scanner rejections as idempotent `REJECTED` redemption rows while keeping invalid, unresolved, malformed, and cross-tenant failures audit-only.
- COMPLETED: Reworked override to append a linked `OVERRIDDEN` redemption through `original_redemption_id`, preserve the original rejection, decrement entitlement once, and audit outcomes.
- COMPLETED: Added redemption filters, detail page, reason-required override/reversal forms, and original/override record links.
- COMPLETED: Added browser camera scanning with `html5-qrcode` 2.3.8, camera selector, start/stop lifecycle, local payload parsing, duplicate callback debounce, permission/unsupported feedback, and manual fallback.
- COMPLETED: Added participant QR administration UI and routes for view, generate, rotate, revoke, one-time QR display, and lifecycle history.
- COMPLETED: Added scanner payload unit tests and expanded Phase 4 feature tests for persisted rejection, audit-only invalid QR, append-only override, participant QR administration, and scanner UI controls.

### Tests And Validation Executed

| Command | Result |
|---|---|
| `php artisan optimize:clear` | Exit 0 |
| `php artisan migrate:status` | Exit 0 before Phase 4 edits |
| `php artisan route:list` | Exit 0; 133 routes after Phase 4 routes |
| `php artisan test` before Phase 4 edits | Exit 0; 27 tests passed, 88 assertions |
| `php artisan migrate --force` | Exit 0; Phase 4 migration applied |
| `php artisan migrate:fresh --force` | Exit 0 |
| `php artisan db:seed --force` | Exit 0 |
| `php artisan scanner:idempotency-cleanup --dry-run` | Exit 0 |
| `php artisan test tests\\Feature\\PhaseFourQRRedemptionTest.php` | Exit 0; 6 tests passed, 32 assertions |
| `php artisan test tests\\Feature\\PhaseFourQRRedemptionTest.php` after final remediation | Exit 0; 10 tests passed, 59 assertions |
| `php artisan test tests\\Feature\\PhaseFourConcurrencyTest.php` | Exit 0; 1 true process-based concurrency test passed, 15 assertions |
| `php artisan test` after final remediation | Exit 0; 38 tests passed, 162 assertions |
| `npm run test:scanner` | Exit 0; scanner payload and debounce tests passed |
| `npm run build` after camera integration | Exit 0; Vite build completed |
| `php artisan migrate:fresh --force` after final remediation | Exit 0 |
| `php artisan db:seed --force` after final remediation | Exit 0 |
| `php artisan migrate:rollback --force` then `php artisan migrate --force` after final remediation | Exit 0 |
| `./vendor/bin/pint` after final remediation | Exit 0; formatting applied |
| `./vendor/bin/pint --test` after final remediation | Exit 0 |

### Known Risks

- Raw participant QR tokens are intentionally available only at issuance time; operators must regenerate credentials if a token page is lost.
- Super-admin scanner API use requires an active tenant context strategy; normal hotel users are fully hotel-scoped.
- Rejected scans without a resolvable participant/session remain audit-only by design because creating redemption rows without safe tenant/identity context would weaken isolation.
- Camera hardware behavior still depends on browser/device permission support; payload parsing, debouncing, and build are automated, with hardware verification documented in operations docs.

## Phase 2 Execution Status

**Current Phase:** Phase 2 - PostgreSQL Migration  
**Current Status:** COMPLETED

### Completed Work

- COMPLETED: Read `docs/phases/phase-2-postgresql-migration.md` as the authoritative Phase 2 scope.
- COMPLETED: Verified Phase 1 artifacts show the application boots, routes register, and baseline tests pass.
- COMPLETED: Re-audited the active existing database as PostgreSQL `head_counter`, not MySQL.
- COMPLETED: Confirmed local Laravel database driver is `pgsql` and PostgreSQL reports version 12.2 through the app connection.
- COMPLETED: Updated committed defaults to PostgreSQL in `.env.example`, `config/database.php`, and `phpunit.xml`.
- COMPLETED: Added isolated `mysql_legacy` connection settings as an optional fallback only if a real MySQL source is later discovered.
- COMPLETED: Converted clean-build migrations to PostgreSQL-compatible timestamps, numeric package prices, unique business keys, and bigint QR relationship types.
- COMPLETED: Added compatibility migration for already-migrated PostgreSQL development schemas.
- COMPLETED: Added required foreign keys and indexes.
- COMPLETED: Seeded canonical room statuses: `AVAILABLE`, `RESERVED`, `OCCUPIED`, `CLEANING`, `MAINTENANCE`, `INACTIVE`.
- COMPLETED: Added and ran an existing-database migration to upsert all six canonical room statuses into PostgreSQL `head_counter`.
- COMPLETED: Converted package seed prices to numeric values and added a decimal model cast.
- COMPLETED: Made seeders idempotent where required for PostgreSQL rebuilds.
- COMPLETED: Added `php artisan db:migrate-mysql-to-pgsql` with dry-run validation, price normalization, status mapping, duplicate checks, orphan checks, batching, upserts, and sequence resets.
- COMPLETED: Added `php artisan db:audit-postgresql-phase2` to audit the actual existing PostgreSQL database for duplicates, orphans, and missing required FKs.
- COMPLETED: Added PostgreSQL-focused feature tests for decimal price storage, canonical room status relationships, legacy FK relationships, and test DB driver.
- COMPLETED: Re-ran `migrate:fresh` and `db:seed` for existing PostgreSQL `head_counter`, clearing the previous orphan QR row and allowing all required FKs to be enforced.
- COMPLETED: Verified `php artisan db:audit-postgresql-phase2` passes against existing PostgreSQL `head_counter`.
- COMPLETED: Verified PostgreSQL catalog contains all required Phase 2 application foreign keys and indexes.
- COMPLETED: Produced `docs/MYSQL_TO_POSTGRESQL_MIGRATION.md`.
- COMPLETED: Recorded Phase 2 decisions in `docs/progress/DECISIONS.md`.

### Deferred Work

- DEFERRED: Broad Indonesian-to-English column renames are deferred to Phase 3 because runtime references are widespread.
- DEFERRED: `count_qr` remains the legacy package QR multiplier. Entitlement modeling remains Phase 4.
- DEFERRED: `mac_address` remains the participant fingerprint storage column until the attendance/participant model is refactored.

### Blocked Work

- BLOCKED: No Phase 2 blocker remains.

### Tests And Validation Executed

| Command | Result |
|---|---|
| `php artisan optimize:clear` | Exit 0 after a transient initial Windows cache rename failure |
| `php artisan migrate:status` | Exit 0; current local database had one pending pre-Phase-2 migration before work |
| `php artisan route:list` | Exit 0; 56 routes registered |
| `php artisan test` before Phase 2 edits | Exit 0; 9 tests passed, 14 assertions |
| `php artisan migrate:fresh --force` against `head_counter_test` PostgreSQL | Exit 0 |
| `php artisan db:seed --force` against `head_counter_test` PostgreSQL | Exit 0 |
| `php artisan migrate:rollback --force` then `php artisan migrate --force` against `head_counter_test` PostgreSQL | Exit 0 |
| `php artisan test` after Phase 2 edits | Exit 0; 13 tests passed, 18 assertions |
| `./vendor/bin/pint` | Exit 0 |
| `php artisan test` after Pint | Exit 0; 13 tests passed, 18 assertions |
| `php artisan migrate --force` against existing local `head_counter` PostgreSQL before fresh rebuild | Exit 0; skipped QR FK because one orphan QR row existed at that time |
| `php artisan migrate --force` against existing local `head_counter` PostgreSQL after re-audit | Exit 0; canonical status migration applied |
| `php artisan db:audit-postgresql-phase2` before fresh rebuild | Exit 1 against existing `head_counter`; reported one QR orphan and missing `qr_detail_meeting_id_foreign` |
| `php artisan migrate:status` after user reran `migrate:fresh` and `db:seed` | Exit 0; all migrations ran in batch 1 |
| `php artisan db:audit-postgresql-phase2` after fresh rebuild | Exit 0 against existing `head_counter`; no orphans, duplicates, or missing required FKs |
| `php artisan route:list` after completion | Exit 0; 56 routes registered |
| `php artisan optimize:clear` after completion | Exit 0 |
| `php artisan migrate:fresh --force`, `php artisan db:seed --force`, `php artisan db:audit-postgresql-phase2`, `php artisan test` against `head_counter_test` | Exit 0; 13 tests passed, 18 assertions |
| `./vendor/bin/pint` after completion | Exit 0 |

### PostgreSQL Catalog Validation

- Confirmed 10 foreign keys in existing PostgreSQL `head_counter`, including all six required Phase 2 application foreign keys plus Spatie permission FKs.
- Confirmed required indexes for users, clients, schedules, packages, rooms, statuses, QR detail, and attendance in existing PostgreSQL `head_counter`.

### Files Changed In Phase 2

- `.env.example`
- `app/Console/Commands/MigrateMysqlToPostgresql.php`
- `app/Enums/RoomStatusEnum.php`
- `app/Http/Controllers/Auth/RegisterController.php`
- `app/Models/Module/MasterData/Package.php`
- `app/Models/Transaction/QRDetail.php`
- `app/Models/User.php`
- `config/database.php`
- `database/migrations/*`
- `database/seeders/*`
- `docs/MYSQL_TO_POSTGRESQL_MIGRATION.md`
- `docs/progress/CURRENT_STATUS.md`
- `docs/progress/DECISIONS.md`
- `phpunit.xml`
- `tests/Feature/PhaseOneSmokeTest.php`
- `tests/Feature/PostgresqlMigrationTest.php`

### Known Risks

- Restrictive delete foreign keys may surface legacy orphan or deletion behavior that was previously allowed by the database.
- PostgreSQL 12.2 was validated locally; production should use a maintained PostgreSQL version where possible.
- Phase 3 still needs the planned domain refactor, sequence-safe transaction numbering, and broader business modeling.

## Phase 3 Execution Status

**Current Phase:** Phase 3 - Core Domain Refactor  
**Current Status:** COMPLETED

### Completed Work

- COMPLETED: Verified Phase 2 PostgreSQL baseline; fixed the PHPUnit password override so tests connect to PostgreSQL.
- COMPLETED: Added canonical Phase 3 domain tables: `hotels`, `meeting_rooms`, `clients`, `bookings`, `meeting_events`, `meeting_packages`, `package_entitlements`, `meeting_package_assignments`, `participants`, and `meeting_attendances`.
- COMPLETED: Added `users.hotel_id` and a default `DEMO` hotel context.
- COMPLETED: Added PostgreSQL constraints and indexes, including `btree_gist` and a partial exclusion constraint for active meeting room overlaps.
- COMPLETED: Added domain models under `app/Domain/*`.
- COMPLETED: Added Phase 3 enums for hotel, room, booking, meeting, entitlement, participant, and attendance states.
- COMPLETED: Added tenant context support with `SetTenantScope` middleware and `ScopeByHotel` trait.
- COMPLETED: Added policies for hotels and hotel-scoped domain resources.
- COMPLETED: Added Form Requests for Phase 3 create/update/status/registration inputs.
- COMPLETED: Added Actions and Services for client creation, meeting create/update, room assignment/conflict checks, meeting status transitions, and participant registration.
- COMPLETED: Added Phase 3 seeder to synchronize legacy seed data into canonical domain tables without dropping legacy tables.
- COMPLETED: Added new web routes for `/hotels`, `/meeting-rooms`, `/clients`, `/bookings`, `/meetings`, `/participants`, and `/packages`.
- COMPLETED: Added `/api/v1/user`, `/api/v1/meetings`, and `/api/v1/participants` routes.
- COMPLETED: Preserved existing legacy routes under `master-data/*` and `transaction/*`.
- COMPLETED: Added minimal Bootstrap-compatible domain Blade views so new domain endpoints render.
- COMPLETED: Added focused Phase 3 feature tests for cross-hotel room isolation, room conflict checks, PostgreSQL exclusion constraint, meeting lifecycle transitions, and participant duplicate detection.
- COMPLETED: Replaced demo-only tenant seed data with real Central Jakarta hotel tenants: Oria Hotel Jakarta, Ashley Hotel Wahid Hasyim, AONE Hotel Jakarta, and Morrissey Hotel Residences.
- COMPLETED: Changed the platform admin seed to `superadmin` with the `Super Admin` role and no forced hotel tenant.
- COMPLETED: Added hotel users for every seeded hotel with `General Manager`, `Hotel Admin`, and `Front Office` roles.
- COMPLETED: Added seeded hotel rooms, clients, bookings, meeting events, packages, entitlements, package assignments, and participants for every seeded hotel.
- COMPLETED: Added tests proving the real hotel seed data, hotel users, platform super-admin role, and super-admin tenant switching.
- COMPLETED: Replaced placeholder canonical views with Bootstrap 4 CRUD workflows for hotels, meeting rooms, clients, bookings, meetings, packages, and participants.
- COMPLETED: Added canonical DataTables-ready table markup and AJAX-safe validation/flash feedback in domain partial views.
- COMPLETED: Added super-admin tenant switching UI in the navbar and `/tenant-switch` page.
- COMPLETED: Added production-oriented `php artisan headcounter:migrate-phase-three-domain` with `--dry-run`, `--validate-only`, `--batch`, and `--resume` options.
- COMPLETED: Added Phase 3 migration reports for source rows, target rows, migrated rows, skipped rows, duplicates, orphans, null required fields, unmapped statuses, invalid foreign keys, business-key mismatches, and failed rows.
- COMPLETED: Finalized meeting lifecycle rules. Terminal recovery from `COMPLETED`, `CANCELLED`, and `NO_SHOW` is forbidden through normal forms and requires a future dedicated administrative recovery action if the business later approves it.
- COMPLETED: Finalized room status synchronization, including recalculation on cancelled/no-show meetings so another active meeting can keep the room reserved or occupied.
- COMPLETED: Expanded Phase 3 feature tests for canonical CRUD UI paths, cross-hotel relation validation, attendance duplicate check-in prevention, super-admin switcher UI, and migration command modes.
- COMPLETED: Added `docs/ARCHITECTURE.md`, `docs/BUSINESS_FLOW.md`, `docs/DATABASE_SCHEMA.md`, and `docs/PHASE_3_LEGACY_MIGRATION.md` with Mermaid diagrams.

### Deferred Work

- DEFERRED: Phase 4 QR credentials, scanner endpoints, redemption, meal sessions, participant entitlement balances, and idempotent scanner processing.
- DEFERRED: Phase 5 audit trail and dedicated administrative recovery workflow for terminal meeting states, if the business approves recovery semantics later.

### Tests And Validation Executed

| Command | Result |
|---|---|
| `php artisan optimize:clear` | Exit 0 |
| `php artisan migrate:status` | Exit 0 before Phase 3 edits |
| `php artisan route:list` | Exit 0; 115 routes registered after final Phase 3 routes |
| `php artisan test` before Phase 3 edits | Exit 1 due missing PostgreSQL test password |
| `php artisan test` after PHPUnit fix | Exit 0; 13 tests passed, 18 assertions |
| `php artisan migrate:fresh --force` | Exit 0; Phase 3 schema and exclusion constraint created |
| `php artisan db:seed --force` | Exit 0; Phase 3 seeder completed |
| `php artisan migrate:rollback --force` | Exit 0; clean-batch rollback validated |
| `php artisan migrate --force` | Exit 0; schema restored after rollback |
| `php artisan test` after Phase 3 implementation | Exit 0; 20 tests passed, 33 assertions |
| `./vendor/bin/pint` | Exit 0; formatting applied |
| `php artisan test` after Pint | Exit 0; 20 tests passed, 33 assertions |
| `npm run build` | Exit 0; Vite build completed |
| `php artisan test --filter=PhaseThreeDomainTest` after real hotel seed update | Exit 0; 9 tests passed, 39 assertions |
| `php artisan db:seed --force` after real hotel seed update | Exit 0; real hotel/user/domain seed data completed |
| `php artisan test` after final Phase 3 remediation | Exit 0; 27 tests passed, 88 assertions |
| `php artisan headcounter:migrate-phase-three-domain --dry-run` | Exit 0 |
| `php artisan headcounter:migrate-phase-three-domain --validate-only` | Exit 0 |
| `php artisan headcounter:migrate-phase-three-domain --resume` | Exit 0 |
| `php artisan migrate:fresh --force` after final remediation | Exit 0 |
| `php artisan db:seed --force` after final remediation | Exit 0 |
| `php artisan migrate:rollback --force` after final remediation | Exit 0 |
| `php artisan migrate --force` after rollback | Exit 0 |
| `npm run build` after final remediation | Exit 0 |

### Files Changed In Phase 3

- `app/Actions/*`
- `app/Domain/*`
- `app/Enums/*`
- `app/Exceptions/DomainException.php`
- `app/Http/Controllers/*`
- `app/Http/Middleware/SetTenantScope.php`
- `app/Http/Requests/*`
- `app/Policies/*`
- `app/Services/*`
- `app/Support/Tenancy/*`
- `database/migrations/2026_06_20_020000_create_phase_three_domain_tables.php`
- `database/seeders/PhaseThreeDomainSeeder.php`
- `resources/views/domain/*`
- `routes/api.php`
- `routes/web.php`
- `tests/Feature/PhaseThreeDomainTest.php`
- `tests/Feature/PhaseThreeCompletionTest.php`
- `app/Console/Commands/MigratePhaseThreeDomain.php`
- `docs/ARCHITECTURE.md`
- `docs/BUSINESS_FLOW.md`
- `docs/DATABASE_SCHEMA.md`
- `docs/PHASE_3_LEGACY_MIGRATION.md`
- `phpunit.xml`

### Known Risks

- Legacy routes and tables remain for backward compatibility. Canonical routes are active; compatibility code should be removed only after a later deprecation phase.
- The development rollback validation rolled back a full clean batch, then `migrate --force` restored the schema; production rollback should be planned per deployment batch.
- The exclusion constraint depends on PostgreSQL `btree_gist`; production roles must be allowed to create or have this extension pre-installed.
- Platform login seed is now `superadmin` / `superadmin123456`; hotel users use `password123456` for local development seed data only.
