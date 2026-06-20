# Phase 1 — Audit and Stabilization

## Objective

Audit the existing repository, identify all broken flows, security risks, and technical debt. Fix critical runtime errors. Normalize environment configuration. Add baseline tests. Document the current state. Produce `docs/CODEBASE_AUDIT.md`.

**Do not implement new business features in this phase.** Do not migrate to PostgreSQL yet. Do not change the schema. Fix only what is broken or critically insecure.

---

## Starting Point

- Laravel 10, PHP ^8.1, MySQL (currently configured)
- Spatie Laravel Permission ^6.2 (installed,Authorization partially wired)
- SimpleSoftwareIO QR Code ~4 (installed)
- Yajra DataTables 10.0 (installed)
- Laravel UI 4.2 (Bootstrap-based auth scaffolding)
- Laravel Sanctum ^3.3 (installed, stateful middleware commented out)
- Frontend: Bootstrap 4, jQuery, Ample Admin theme, custom SPA routing via `core.js`

---

## Current State Summary

### Working Features (partially)

| Feature | Status | Notes |
|---------|--------|-------|
| Client CRUD | Partial | List and store work; edit view is empty stub |
| Meeting Schedule CRUD | Partial | Create/list/edit work; no conflict prevention; QR is base64-encoded trx_number (insecure) |
| Meeting Attendance | Partial | Registration via QR works but uses IP for duplicate check; quota check exists |
| QR Generation | Working | Generates PNG QR codes for meetings and participants; tokens are predictable |
| Role/Permission Management | Partial | List/manage works; no authorization enforced on most endpoints |
| Dashboard | Placeholder | Hardcoded demo data, not wired to real data |

### Broken or Incomplete

| Item | Issue |
|------|-------|
| Client edit | Controller method is empty stub |
| Permission edit/destroy | Controller methods are empty stubs |
| Report routes | `report.php` is empty |
| User management | No controller or routes beyond auth scaffolding |
| Packages | Model exists with `count_qr` but no entitlement model |
| Room status lifecycle | Only 3 statuses (Available/Booked/Occupied); no room scheduling or conflict checks |
| Meeting lifecycle | No state machine; statuses are not tracked |
| QR token security | Tokens are base64-encoded transaction numbers — trivially forgeable |
| Duplicate attendance | Uses `$_SERVER['REMOTE_ADDR']`; unreliable behind NAT |
| Transaction numbering | Race condition in `generateTransactionNumber` |
| Cross-tenant isolation | No hotel scoping exists; single-tenant only |
| API authentication | Sanctum stateful middleware commented out |
| Audit trail | None |
| Meal sessions / entitlements / redemptions | Do not exist |
| Scanner API | Does not exist |

---

## Tasks

### 1.1 Full Codebase Audit

Produce `docs/CODEBASE_AUDIT.md` documenting:

- Current architecture (directories, models, controllers, routes)
- Technology versions (Laravel 10, PHP 8.1+, MySQL, Bootstrap 4, jQuery)
- Module inventory (what exists, what works, what is broken)
- Security risks (mass assignment, exposed stack traces, predictable QR tokens, no authorization)
- Data integrity risks (no foreign keys, race conditions, no transactions in critical paths)
- MySQL-specific SQL (if any raw queries exist)
- Performance risks (N+1 queries, missing indexes)
- Technical debt (stub methods, duplicated helpers, dd() in production code)
- Recommended target architecture
- Proposed phased roadmap (this document)

### 1.2 Critical Bug Fixes

| Fix | File(s) | Description |
|-----|---------|-------------|
| Validation rule bug | `ClientController.php` | Comma separates `'required'` from `'max:3'`, breaking the rule |
| Error response leaks | All controllers | Replace `$th` exposure with generic error messages |
| `abort('404')` | `DashboardController.php` | Change string to integer `abort(404)` |
| `dd()` in helper | `DataAccessHelpers.php` | Replace `dd($e)` with proper exception handling |
| Duplicate helper methods | `DataAccessHelpers.php` | `convertArrayToNumber` and `convertToNumber` are identical; consolidate |
| Transaction wrapping | `MeetingScheduleController::store` | Wrap multi-table creation + file writes in `DB::transaction()` |
| QR update bug | `MeetingScheduleController::update` | Fix room availability toggle logic and QR filename collision |

### 1.3 Security Quick Wins

| Item | Description |
|------|-------------|
| Remove stack trace exposure | All controller error responses |
| Add `$fillable` to all models | Replace `$guarded = []` with explicit `$fillable` arrays |
| Fix predictable QR tokens | Minimum: add random component; full fix in Phase 4 |
| Enable Sanctum stateful middleware | Uncomment in `Kernel.php` |
| Add authorization middleware | Apply `auth` and permission middleware to all route groups |
| Rate limit attendance form | Prevent brute-force QR scanning |

### 1.4 Environment Normalization

- Update `.env.example` to document all required variables
- Ensure `.env` has `APP_TIMEZONE=Asia/Jakarta`
- Verify queue, cache, and session configurations are set
- Document required PHP extensions

### 1.5 Baseline Tests

Write smoke tests that verify the application does not crash:

| Test | Description |
|------|-------------|
| Guest redirect | Unauthenticated user is redirected to login |
| Login | User can log in with valid credentials |
| Client list | Authenticated user sees client list |
| Meeting schedule list | Authenticated user sees schedule list |
| Attendance form access | Attendance form route returns 200 for valid QR |
| 404 for invalid QR | Invalid QR token returns appropriate error |
| Permission middleware | Unauthenticated user cannot access master-data routes |

### 1.6 Normalize Existing Code (Non-Breaking)

- Add `$fillable` arrays to all models
- Add `$timestamps = true` where currently `false` (or document intentional choice)
- Add explicit table names already set via `$table` property (keep)
- Fix model relationship method names to follow Laravel conventions (`ruangan` -> `room`, `paket` -> `package`)
- Add return types to controller methods
- Fix `MeetingAttendance` relation typo (`trx_metting_number` column name noted; do not rename column yet, but add comment)

---

## Completion Checklist

- [ ] `docs/CODEBASE_AUDIT.md` produced
- [ ] All critical bugs fixed
- [ ] Security quick wins applied
- [ ] `.env.example` updated
- [ ] Baseline tests pass (`php artisan test`)
- [ ] Code formatted (`./vendor/bin/pint`)
- [ ] No new regressions introduced
- [ ] Progress documented in `docs/progress/CURRENT_STATUS.md`

---

## Exit Criteria

Before starting Phase 2:

1. All existing routes return valid HTTP responses (not 500)
2. Baseline tests pass
3. No stack traces or raw errors leaked to clients
4. All models have explicit `$fillable`
5. Audit document is complete and reviewed