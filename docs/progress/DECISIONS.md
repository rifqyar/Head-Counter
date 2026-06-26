# Architectural Decisions

**Last Updated:** 2026-06-26

## AD-001: Phase-Based Implementation Strategy

**Date:** 2026-06-19
**Status:** Accepted

The master specification is split into 8 sequential phases. Each phase must be completed and verified before proceeding to the next. Phases are documented in `docs/phases/phase-{N}-{slug}.md`.

**Rationale:** The codebase is partially implemented. Incremental phases allow stabilization before major changes, and each phase produces testable milestones.

---

## AD-002: Preserve Bootstrap 4 + jQuery Frontend

**Date:** 2026-06-19
**Status:** Accepted

The existing frontend uses Bootstrap 4, jQuery, Ample Admin theme, DataTables, Select2, and a custom SPA routing mechanism via `core.js`. The frontend will not be rewritten. New features will use the same patterns and libraries.

**Rationale:** The master plan says "Do not replace the existing frontend technology unless there is a strong documented reason." No such reason currently exists. Rewriting the frontend would add significant risk and delay.

---

## AD-003: MySQL to PostgreSQL Migration Before Domain Refactor

**Date:** 2026-06-19
**Status:** Accepted

PostgreSQL migration (Phase 2) is performed before the domain refactor (Phase 3) to allow the new domain models to use PostgreSQL-native features (exclusion constraints, JSONB, tstzrange) from the start.

**Rationale:** Building the domain models on MySQL first would require re-migration. Moving to PostgreSQL early ensures all new constraints and types work correctly.

---

## AD-004: Domain-Driven Directory Structure

**Date:** 2026-06-19
**Status:** Accepted

New domain models will be organized under `app/Domain/` with subdirectories per bounded context (Hotel, Booking, Meeting, Participant, Attendance, Catering, QRCode, Redemption, Reporting, Integration). Business logic will be extracted into Action classes under `app/Actions/`.

**Rationale:** Matches the target architecture in the master plan. Keeps controllers thin and groups related logic together while remaining understandable to a normal Laravel development team.

---

## AD-005: Indonesian Column Names to English

**Date:** 2026-06-19
**Status:** Accepted

All database column names will be renamed from Indonesian (tgl_start, jam_mulai, kuota, kd_pck, etc.) to English canonical names as part of the Phase 3 domain refactor. A mapping table will be maintained in the phase document.

**Rationale:** The codebase currently uses a mix of Indonesian and English column names, making it harder for international developers and inconsistent with Laravel conventions. The master specification defines English field names.

---

## AD-006: QR Token Security — Cryptographic Hash

**Date:** 2026-06-19
**Status:** Accepted

Meeting QR tokens and participant QR tokens will use 32-byte cryptographically random tokens. Only the SHA-256 hash will be stored in the database. The raw token will never be logged. The QR payload will use an opaque token URL, never exposing the database ID.

**Rationale:** The current implementation uses base64-encoded transaction numbers, which are trivially predictable. This is a critical security vulnerability.

---

## AD-007: Redemption Based on Entitlements and Sessions, Not Generic Scan Count

**Date:** 2026-06-19
**Status:** Accepted

Redemption will be based on the package entitlement structure and specific meal sessions. A package with "2 coffee breaks + 1 lunch" creates separate entitlements for Coffee Break 1, Coffee Break 2, and Lunch. Each can be redeemed once. A generic scan counter will NOT be used.

**Rationale:** Explicitly stated in the master plan: "Do not use a generic scan-count rule instead of session entitlements." The current `count_qr` field in `m_packages` is a generic counter that does not support session-based redemption.

---

## AD-008: Custom SPA Routing Preserved

**Date:** 2026-06-19
**Status:** Accepted

The existing custom SPA routing via `renderView()` in `core.js` will be preserved. New routes will follow the same pattern: module views return partial HTML fragments, standalone pages (login, attendance form, scanner) return full HTML documents.

**Rationale:** Changing the routing mechanism would require rewriting all existing views and JavaScript. The current approach works and is consistent.

---

## AD-009: Column Rename Strategy During Phase 3

**Date:** 2026-06-19
**Status:** Accepted

Column renames will be performed via dedicated Laravel migration files that use `$table->renameColumn()`. All model, controller, view, and JavaScript references will be updated in the same commit. Old column names will not be maintained as aliases.

**Rationale:** Maintaining dual column names会增加 complexity. A clean rename with all references updated simultaneously is cleaner and avoids confusion.

---

## AD-010: No Live Data Migration in Development

**Date:** 2026-06-19
**Status:** Accepted

During development, migrations will use `migrate:fresh` and seeders. No attempt will be made to preserve the existing MySQL data in the development environment. The MySQL-to-PostgreSQL data migration strategy will be documented separately for production use.

**Rationale:** Current data appears to be development/test data. Production migration scripts will be created as part of Phase 2 documentation but only applied to production during a controlled deployment.

---

## AD-011: Phase 1 QR Hardening Without Schema Changes

**Date:** 2026-06-20
**Status:** Accepted

Newly generated meeting QR files include a random filename component, and the public attendance URL includes a `qr_token` derived from that persisted filename. Attendance form access validates the QR detail ID, meeting ID, validity window, and token when present.

**Rationale:** Phase 1 forbids schema changes, so the final hash-only opaque token architecture cannot be implemented yet. This is a backward-compatible mitigation that improves newly generated QR links while preserving existing QR records and routes.

---

## AD-012: Phase 1 Duplicate Attendance Mitigation

**Date:** 2026-06-20
**Status:** Accepted

Duplicate attendance checks no longer use requester IP address. The existing `mac_address` column now stores a SHA-256 fingerprint of submitted meeting number, company, participant name, and phone number.

**Rationale:** IP address is unreliable behind NAT and proxies. Phase 1 cannot add a participant identity table or new columns, so a hash of existing form fields is the safest non-breaking mitigation until the Phase 4/5 attendance model is redesigned.

---

## AD-013: Phase 2 Modifies Legacy Migrations and Adds Compatibility Migration

**Date:** 2026-06-20
**Status:** Accepted

Phase 2 updates existing migration definitions for clean PostgreSQL `migrate:fresh` builds and adds `2026_06_19_235959_prepare_existing_postgresql_schema.php` for already-migrated PostgreSQL development databases.

**Rationale:** The project is still pre-production, so clean rebuilds may use corrected legacy migrations. A compatibility migration prevents local already-migrated databases from missing email, numeric price, status, index, and timestamp conversions.

---

## AD-014: Phase 2 Defers Broad Column Renames

**Date:** 2026-06-20
**Status:** Accepted

Indonesian legacy column names remain in Phase 2. The mapping to English canonical names is documented in `docs/MYSQL_TO_POSTGRESQL_MIGRATION.md` and remains targeted for Phase 3.

**Rationale:** Repository search showed wide use in controllers, models, Blade views, JavaScript, and tests. Renaming now would increase behavior risk without being required for PostgreSQL compatibility.

---

## AD-015: Canonical Room Status Codes

**Date:** 2026-06-20
**Status:** Accepted

Room statuses now use `AVAILABLE`, `RESERVED`, `OCCUPIED`, `CLEANING`, `MAINTENANCE`, and `INACTIVE`. Legacy `001`, `002`, `003`, `Available`, `Booked`, and `Occupied` values are mapped during compatibility migration and import.

**Rationale:** Canonical string codes are clearer, PostgreSQL-friendly, and still preserve current room availability behavior.

---

## AD-016: Package Price Precision

**Date:** 2026-06-20
**Status:** Accepted

`m_packages.price` uses `numeric(15,2)` / Laravel `decimal(15,2)`.

**Rationale:** Existing seed values are rupiah-style package prices and do not require more precision. Invalid values are reported by the import command instead of silently converted to zero.

---

## AD-017: Foreign-Key Delete Behavior

**Date:** 2026-06-20
**Status:** Accepted

Historical attendance and QR relationships use restrictive deletes. Nullable schedule references to package and room use null-on-delete.

**Rationale:** The application should not cascade-delete historical attendance or QR data. Nullable package/room references avoid destructive cleanup while preserving schedule records.

---

## AD-018: Data Migration Mechanism

**Date:** 2026-06-20
**Status:** Accepted

If a true MySQL source is later discovered, existing MySQL data can be migrated through `php artisan db:migrate-mysql-to-pgsql`, using the isolated `mysql_legacy` source connection and the primary `pgsql` target connection.

**Rationale:** A Laravel command can reuse application configuration, batch records, normalize legacy data, report validation errors, and avoid logging sensitive values.

---

## AD-019: Existing Database Reclassified as PostgreSQL

**Date:** 2026-06-20
**Status:** Accepted

The existing application database is PostgreSQL `head_counter`, not MySQL. Phase 2 is therefore treated as PostgreSQL schema stabilization and constraint enforcement, with MySQL import retained only as an optional fallback.

**Rationale:** Re-audit of `.env`, Laravel connection metadata, migration status, and PostgreSQL catalog confirms the active database is `pgsql` / `head_counter`.

---

## AD-020: Existing Dirty QR Data Blocks One Foreign Key

**Date:** 2026-06-20
**Status:** Accepted

The existing PostgreSQL database has one orphan QR row: `qr_detail.id=15` references missing `trx_meeting_schedule.id=5`. The FK migration skips only `qr_detail_meeting_id_foreign` when this dirty data is present. Clean PostgreSQL builds still create the QR foreign key.

**Rationale:** Deleting or remapping existing QR data is a data-owner decision. The migration should not silently discard records, but it should expose the exact blocker and allow the rest of Phase 2 constraints to apply.

---

## AD-021: Phase 3 Canonical Tables Added Beside Legacy Tables

**Date:** 2026-06-20
**Status:** Accepted

Phase 3 creates new canonical tables (`hotels`, `meeting_rooms`, `clients`, `bookings`, `meeting_events`, `meeting_packages`, `package_entitlements`, `meeting_package_assignments`, `participants`, `meeting_attendances`) beside the legacy tables instead of renaming or dropping legacy tables immediately.

**Rationale:** The existing Bootstrap/jQuery screens and DataTables still depend on legacy routes and field names. Parallel canonical tables allow the domain model, tenant isolation, and constraints to be introduced while preserving old behavior until the UI is migrated.

---

## AD-022: Default Hotel Migration Context

**Date:** 2026-06-20
**Status:** Accepted

Legacy records without hotel context are assigned to a default `DEMO` hotel during Phase 3 seeding. Existing users with `hotel_id = null` are assigned to the same default hotel.

**Rationale:** Legacy data is single-tenant. A deterministic default hotel gives every hotel-scoped canonical row a valid tenant without fabricating multiple hotels.

---

## AD-023: Legacy Schedule Booking Strategy

**Date:** 2026-06-20
**Status:** Accepted

Each legacy `trx_meeting_schedule.trx_number` is mirrored as a canonical `bookings.booking_number` with source `LEGACY`, then linked to the corresponding `meeting_events` row.

**Rationale:** Phase 3 requires meeting events to link to bookings. Legacy schedules do not have a separate booking aggregate, so one compatibility booking per legacy transaction preserves the business key and referential integrity.

---

## AD-024: Tenant Scope Bypass Rules

**Date:** 2026-06-20
**Status:** Accepted

`ScopeByHotel` applies a global hotel filter only when `TenantContext` contains a hotel and no explicit bypass is active. CLI work with no tenant context remains unfiltered; HTTP routes use `SetTenantScope` to derive context from the authenticated user or an authorized super-admin session context.

**Rationale:** This prevents accidental cross-tenant access during requests while avoiding broken migrations, seeders, and maintenance commands when no tenant exists.

---

## AD-025: Room Conflict Constraint Design

**Date:** 2026-06-20
**Status:** Accepted

Room conflicts are prevented at application level by `MeetingRoomConflictService` and at database level by a PostgreSQL `btree_gist` exclusion constraint on `meeting_room_id` and `tstzrange(start_at, end_at, '[)')`, filtered to statuses other than `CANCELLED` and `NO_SHOW`.

**Rationale:** The application needs friendly validation errors, but the database must still protect against concurrent overlapping inserts. The half-open range allows adjacent meetings where one ends exactly when the next starts.

---

## AD-026: Package Count QR Compatibility

**Date:** 2026-06-20
**Status:** Accepted

Legacy `m_packages.count_qr` is mirrored into a `CUSTOM` package entitlement quantity and retained in `meeting_packages.metadata.legacy_count_qr`.

**Rationale:** Phase 3 can define package entitlements, but participant entitlement balances and redemption are Phase 4. The compatibility mapping preserves legacy package meaning without implementing redemption behavior early.

---

## AD-027: Participant Duplicate Detection

**Date:** 2026-06-20
**Status:** Accepted

Participant duplicates are detected within the same meeting by normalized email, normalized phone, or `identity_reference`. Empty values are ignored. PostgreSQL partial unique indexes enforce each non-null identity key per meeting.

**Rationale:** This replaces the old IP/fingerprint-based attendance identity with tenant-safe participant identity rules while allowing incomplete participant records.

---

## AD-028: Attendance Check-In Constraint

**Date:** 2026-06-20
**Status:** Accepted

Canonical `meeting_attendances` stores separate attendance events and uses a partial unique index to prevent more than one `MEETING_CHECKIN` row per participant.

**Rationale:** Phase 3 separates participants from attendance events and prevents duplicate check-ins without implementing Phase 4 scanner/redemption flows.

---

## AD-029: Real Jakarta Hotel Tenant Seed Data

**Date:** 2026-06-20
**Status:** Accepted

Phase 3 seed data uses real Central Jakarta hotel tenants around Jl. K.H. Wahid Hasyim: Oria Hotel Jakarta, Ashley Hotel Wahid Hasyim, AONE Hotel Jakarta, and Morrissey Hotel Residences. Oria Hotel Jakarta is the default legacy migration hotel context.

**Rationale:** Phase 3 is a multi-hotel domain refactor, so realistic tenant data is more useful than a generic `DEMO` hotel. The seed data remains development-safe by using test emails and passwords while preserving real hotel names and addresses.

---

## AD-030: Platform Super Admin Role

**Date:** 2026-06-20
**Status:** Accepted

The old generic admin seed is replaced by a platform-level `superadmin` user with the `Super Admin` role and `hotel_id = null`. Super admin can access all tenant data by default or switch into a specific tenant context through the session-backed `tenant_hotel_id`.

**Rationale:** A platform administrator should not belong to one hotel. Keeping `hotel_id` null and using an explicit super-admin role separates platform access from hotel-scoped user access.

---

## AD-031: Seeded Hotel Operations Roles

**Date:** 2026-06-20
**Status:** Accepted

Every seeded hotel receives `General Manager`, `Hotel Admin`, and `Front Office` users. These users are scoped to their hotel through `users.hotel_id` and assigned Spatie roles with hotel-operation permissions.

**Rationale:** Tenant isolation and role behavior are easier to validate with realistic hotel users per tenant instead of a single global administrator.

---

## AD-032: Phase 3 Safe Delete Behavior

**Date:** 2026-06-20
**Status:** Accepted

Phase 3 uses safe operational state changes instead of destructive deletes for hotels, meeting rooms, bookings, meetings, packages, and participants. Hotels and rooms are marked inactive, bookings and meetings are cancelled, packages are deactivated, and participants are cancelled. Clients may still be deleted when no dependent operational records block the delete.

**Rationale:** Operational and historical records should not disappear from a hotel meeting system. Safe status changes preserve auditability until Phase 5 adds a broader audit trail.

---

## AD-033: Canonical UI Migration Completed With Compatibility Routes Preserved

**Date:** 2026-06-20
**Status:** Accepted

Canonical Phase 3 screens now use `/hotels`, `/meeting-rooms`, `/clients`, `/bookings`, `/meetings`, `/participants`, and `/packages` with canonical attributes and Bootstrap 4 partials. Legacy `master-data/*` and `transaction/*` routes remain as compatibility routes.

**Rationale:** Phase 3 needs canonical domain workflows, but legacy QR/attendance behavior still depends on legacy routes until Phase 4. Preserving compatibility avoids breaking existing bookmarks and QR flows.

---

## AD-034: Phase 3 Legacy Migration Command

**Date:** 2026-06-20
**Status:** Accepted

Production-oriented Phase 3 migration and validation use `php artisan headcounter:migrate-phase-three-domain`. The command supports `--dry-run`, `--validate-only`, `--batch`, and `--resume`, and reports row counts, skipped rows, duplicates, orphans, null required fields, unmapped statuses, invalid foreign keys, business-key mismatches, and failed rows.

**Rationale:** Seeders are useful for development, but production migration needs an explicit command with validation output and rerun-safe behavior.

---

## AD-035: Meeting Recovery Rules

**Date:** 2026-06-20
**Status:** Accepted

Normal Phase 3 lifecycle transitions forbid recovery from `COMPLETED`, `CANCELLED`, and `NO_SHOW`. Recovery scenarios such as `CANCELLED -> SCHEDULED`, `NO_SHOW -> SCHEDULED`, `COMPLETED -> OCCUPIED`, and `COMPLETED -> SCHEDULED` require a future dedicated administrative recovery action, permission, reason, audit trail, room conflict revalidation, and room status recalculation.

**Rationale:** Terminal-state recovery is operationally sensitive and should not be available through ordinary edit forms. The normal lifecycle is complete without recovery.

---

## AD-036: Room Status Recalculation

**Date:** 2026-06-20
**Status:** Accepted

Meeting cancellation and no-show transitions recalculate room status from other active meetings in the same hotel and room. Another occupied meeting keeps the room `OCCUPIED`; another scheduled or check-in-open meeting keeps it `RESERVED`; otherwise the room becomes `AVAILABLE`.

**Rationale:** A room must not be marked available while another active meeting reserves or occupies it.

---

## AD-037: Compatibility Removal Plan

**Date:** 2026-06-20
**Status:** Accepted

Legacy tables, models, routes, and JavaScript remain during Phase 3 for backward compatibility. Removal is deferred until QR/redemption work no longer depends on legacy QR and attendance flows, and after production data migration reports are reviewed.

**Rationale:** Deleting compatibility code in Phase 3 would risk breaking public attendance forms and existing QR behavior, which are explicitly deferred to Phase 4.

---

## AD-038: Phase 4 QR Hash Storage

**Date:** 2026-06-20
**Status:** Accepted

Meeting and participant QR tokens are generated from 32 random bytes, URL-safe encoded, and stored only as SHA-256 hashes plus last-four identifiers. Meeting QR SVG output is stored at issuance time in `meeting_events.meeting_qr_path` because raw tokens cannot be reconstructed from hashes.

**Rationale:** This preserves printable QR operations without weakening the no-raw-token storage rule.

---

## AD-039: Phase 4 Entitlement And Session Strategy

**Date:** 2026-06-20
**Status:** Accepted

Participant entitlements aggregate all assigned package entitlements by entitlement type. Meal sessions are generated as `DRAFT` from package entitlement quantities unless explicitly scheduled/opened by an administrator.

**Rationale:** Package definitions determine available benefit quantities, while session timing still requires human operational control.

---

## AD-040: Phase 4 Redemption Integrity

**Date:** 2026-06-20
**Status:** Accepted

Redemption uses the lock order: idempotency key scope, participant entitlement row, then active redemption lookup/insert. PostgreSQL partial unique index `redemptions_one_active_success` prevents more than one `SUCCESS` or `OVERRIDDEN` redemption for the same participant and meal session.

**Rationale:** Application checks provide friendly responses; row locks and the partial unique index provide database-backed integrity under race conditions.

---

## AD-041: Phase 4 Idempotency And Reversal

**Date:** 2026-06-20
**Status:** Accepted

Scanner idempotency responses are retained for one day and cleaned by `scanner:idempotency-cleanup`. Reversal marks the original redemption `REVERSED`, restores entitlement counters, and allows a later valid redemption because the partial index excludes reversed rows.

**Rationale:** Scanners need retry safety without indefinite storage. Reversal must preserve history while correcting entitlement balances.

---

## AD-042: Phase 4 Override Boundaries

**Date:** 2026-06-20
**Status:** Accepted

Override is action-backed only for operational rejection codes such as `SESSION_EXPIRED`, `QUOTA_EXHAUSTED`, `ALREADY_REDEEMED`, and `NO_ENTITLEMENT`. Invalid QR and cross-hotel failures are not overrideable.

**Rationale:** Overrides should handle controlled service recovery, not bypass identity or tenant boundaries.

---

## AD-043: Phase 4 True Concurrency Test Method

**Date:** 2026-06-20
**Status:** Accepted

True redemption concurrency is tested with two separate PHP processes launched by Symfony Process. Each process runs `scanner:concurrent-redemption-worker`, waits at a filesystem barrier, and then calls the scanner action against the same PostgreSQL participant, meal session, and entitlement.

**Rationale:** This verifies overlapping transactions, PostgreSQL row locks, and the partial unique redemption index. Sequential duplicate tests remain useful but are not a true race.

---

## AD-044: Persisted Rejected Scan Strategy

**Date:** 2026-06-20
**Status:** Accepted

Persist `REJECTED` redemption rows only when participant QR, participant, meeting, meal session, and tenant context are safely resolved and the rejection is operationally overrideable. Keep invalid QR, wrong-hotel, unresolved, malformed, authentication, and authorization failures audit-only.

**Rationale:** Override needs a stable auditable record, but tenant and identity boundaries must not be bypassed by fabricating cross-tenant or unresolved redemption rows.

---

## AD-045: Overrideable And Non-Overrideable Rejection Codes

**Date:** 2026-06-20
**Status:** Accepted

Overrideable persisted codes are `SESSION_NOT_OPEN`, `SESSION_EXPIRED`, `NO_ENTITLEMENT`, `ALREADY_REDEEMED`, `QUOTA_EXHAUSTED`, and `MEETING_COMPLETED`.

Non-overrideable or audit-only codes include `INVALID_QR`, `QR_EXPIRED`, `QR_REVOKED`, `WRONG_HOTEL`, `WRONG_MEETING`, `PARTICIPANT_BLOCKED`, `MEETING_CANCELLED`, authentication failure, authorization failure, and malformed requests.

**Rationale:** Operations may recover service-window and entitlement exceptions. Identity, tenant, revoked credential, blocked participant, and security failures require a separate administrative process.

---

## AD-046: Append-Only Override Design

**Date:** 2026-06-20
**Status:** Accepted

Override creates a new `OVERRIDDEN` redemption linked to the original `REJECTED` row through `original_redemption_id`. The original rejected row is never converted into success. The transaction locks the rejected row and entitlement, rechecks active success, decrements entitlement once, and writes audit logs.

**Rationale:** Append-only history preserves scanner evidence while allowing controlled operational recovery.

---

## AD-047: Scanner Camera Library And Payload Parsing

**Date:** 2026-06-20
**Status:** Accepted

The scanner UI uses `html5-qrcode` 2.3.8 under Apache-2.0. Decoding happens locally in the browser. Supported payloads are raw opaque participant tokens and same-origin `/scan/participant/{token}` URLs. Arbitrary URLs and script-like payloads are rejected.

**Rationale:** The library is lightweight, browser-native, Vite-compatible, and avoids introducing a frontend framework or external frame upload.

---

## AD-048: Scanner Browser Support And Manual Fallback

**Date:** 2026-06-20
**Status:** Accepted

Camera scanning targets modern desktop browsers, Android Chrome, and iOS Safari where camera APIs are available. HTTPS is required in production. Manual token input remains the guaranteed fallback.

**Rationale:** Camera APIs vary by device and permission context; operations must remain functional without camera access.

---

## AD-049: Participant QR One-Time Display

**Date:** 2026-06-20
**Status:** Accepted

Participant QR generate and rotate show the raw token and QR image only in the immediate flash response. Old QR images cannot be reconstructed; lost QR recovery is rotation.

**Rationale:** Raw participant QR tokens are not stored. Flash-only display avoids query-string exposure while giving operators one chance to print or download.

---

## AD-050: Shared Canonical UI Shell For Remediation

**Date:** 2026-06-20
**Status:** Accepted

Canonical admin pages remain `core.js` partials inside `layouts.app`, but shared Blade partials now provide page title, breadcrumbs, cards, validation summary, form actions, Select2 initialization, and duplicate-submit disabling.

**Rationale:** This preserves Bootstrap 4, jQuery, Ample Admin, and the existing AJAX loader while removing the plain/basic look of Phase 3 and Phase 4 screens.

---

## AD-051: Meeting Room Hotel Assignment

**Date:** 2026-06-20
**Status:** Accepted

Meeting rooms remain strictly hotel-owned. Normal hotel users cannot submit `hotel_id`; the active tenant context supplies it. Super-admins may select an active hotel. Room hotel reassignment is blocked once dependent meetings exist.

**Rationale:** This keeps tenant isolation server-side and prevents unsafe reassignment of operational meeting history.

---

## AD-052: Client Hotel Association Compatibility Model

**Date:** 2026-06-20
**Status:** Accepted

`client_hotel` is introduced as the many-to-many association table. Existing `clients.hotel_id` is retained as a transitional primary hotel for compatibility and is backfilled into `client_hotel`.

**Rationale:** Client companies may work with multiple hotels, but removing `clients.hotel_id` immediately would break existing Phase 3/4 code and data assumptions.

---

## AD-053: Booking Client Selector Uses Associations

**Date:** 2026-06-20
**Status:** Accepted

Booking client selectors and validation use active `client_hotel` associations for the active hotel context rather than trusting `clients.hotel_id` alone.

**Rationale:** Shared clients must appear in every associated hotel while hotel-only clients remain isolated.

---

## AD-054: Tenant Switch Feedback And Active Context Indicator

**Date:** 2026-06-20
**Status:** Accepted

Tenant switching validates active hotels, preserves the previous context on failure, redirects to the dashboard on success/reset, and displays the current hotel code in the navbar.

**Rationale:** Operators need immediate confirmation of the active hotel context before using tenant-scoped lists and forms.

---

## AD-055: Phase 5 Canonical RBAC Matrix With Legacy Compatibility

**Date:** 2026-06-20
**Status:** Accepted

Phase 5 introduces canonical uppercase roles (`SUPER_ADMIN`, `HOTEL_ADMIN`, `SALES_ADMIN`, `BANQUET_ADMIN`, `FRONT_OFFICE`, `MEETING_OPERATOR`, `SCANNER_OPERATOR`, `REPORT_VIEWER`, `AUDITOR`) and dot-delimited permissions. Legacy roles and permissions remain synchronized for existing Blade menus and compatibility routes.

**Rationale:** Phase 5 needs explicit enterprise RBAC, but removing legacy permission names would break active Bootstrap/jQuery screens.

---

## AD-056: Policy And Middleware Responsibilities

**Date:** 2026-06-20
**Status:** Accepted

Route middleware provides coarse authentication, tenant, role, permission, and rate-limit gates. Policies provide resource-specific permission plus hotel-scope decisions. Controllers still call policies for resource access.

**Rationale:** Hidden UI checks are insufficient. Defense-in-depth keeps guessed IDs, direct routes, and API calls constrained.

---

## AD-057: Super Admin Tenant Behavior

**Date:** 2026-06-20
**Status:** Accepted

`SUPER_ADMIN` may perform platform operations and can view tenant resources across hotels. When a session tenant context is selected, `SetTenantScope` requires that hotel to exist and be active.

**Rationale:** Platform admins need cross-tenant administration, but selected tenant context must not silently fall back when stale or inactive.

---

## AD-058: Authorization Error Convention

**Date:** 2026-06-20
**Status:** Accepted

Missing permissions return `403`. Tenant-scoped route-model binding may produce `404` when the scoped model cannot be resolved. Validation failures remain `422`, throttling returns `429`, and unauthenticated API calls return `401`.

**Rationale:** This preserves Laravel conventions while avoiding unnecessary disclosure of cross-tenant records.

---

## AD-059: Audit Logging Compatibility And Redaction

**Date:** 2026-06-20
**Status:** Accepted

The existing Phase 4 `audit_logs` table is extended rather than replaced. `AuditLogger` writes both legacy `event/auditable` fields and Phase 5 `action/entity` fields, and recursively redacts sensitive keys.

**Rationale:** Phase 4 redemption audit records stay compatible while Phase 5 gains richer metadata and safer logging.

---

## AD-060: Rate Limiter Strategy

**Date:** 2026-06-20
**Status:** Accepted

Login uses Laravel's built-in username plus IP throttling. Attendance registration is keyed by meeting-token hash plus IP. Scanner validate/redeem are keyed by authenticated operator, hotel, and device identifier. Sensitive admin actions use authenticated user or IP.

**Rationale:** Operational scanner throughput needs higher limits than public forms, while brute-force and QR abuse still need centralized limits.

---

## AD-061: Session, CSP, And CORS Strategy

**Date:** 2026-06-20
**Status:** Accepted

Session security remains environment-driven for secure cookies and SameSite. CSP is configurable through `SECURITY_CSP` and allows only current app assets plus scanner camera support. CORS uses explicit origins from `CORS_ALLOWED_ORIGINS`.

**Rationale:** Local development must keep working, but production can harden settings through environment configuration without code changes.

---

## AD-062: Integration API Key Foundation

**Date:** 2026-06-20
**Status:** Accepted

Integration API keys use a public prefix plus one-time raw secret. Only a Laravel hash of the secret is stored. Keys support hotel scope, abilities, expiration, revocation, and last-used tracking.

**Rationale:** Phase 5 must establish secure authentication primitives for Phase 7 without building the full integration workflow.

---

## AD-063: HMAC Deferred

**Date:** 2026-06-20
**Status:** Accepted

HMAC signing is deferred to Phase 7. The intended headers are `X-API-Key`, `X-Timestamp`, `X-Nonce`, and `X-Signature` with canonical method, path, timestamp, nonce, and body hash.

**Rationale:** API key storage and validation are enough for the Phase 5 foundation; replay windows, nonce storage, and canonical signing should be implemented alongside real integration endpoints.

---

## AD-064: Phase 5 Endpoint Security Matrix

**Date:** 2026-06-21
**Status:** Accepted

Endpoint security is documented by active route family in `docs/ENDPOINT_SECURITY_MATRIX.md`, verified against `php artisan route:list --except-vendor`. Resource routes inherit common auth, tenant, permission, policy, validation, and audit controls rather than duplicating identical rows per generated CRUD action.

**Rationale:** Laravel resource routes expand into many endpoints with the same middleware and policy boundaries. A family matrix is easier to maintain while still accounting for all 149 active application routes.

---

## AD-065: Tenant-Scoped User Management

**Date:** 2026-06-21
**Status:** Accepted

Phase 5 user administration is implemented under `/users`. Hotel administrators can manage only users in their own hotel and cannot assign protected platform roles. Super-admins may manage platform users and protected roles, but the final active super-admin account cannot be deactivated.

**Rationale:** User administration is security-critical tenant data. Server-side role authority checks are required even when UI options are hidden.

---

## AD-066: Active User And Token Revocation Rules

**Date:** 2026-06-21
**Status:** Accepted

Users have explicit active/inactive status. Inactive users are blocked by global middleware and tenant middleware. Deactivating a user revokes personal access tokens, and token management actions are audited.

**Rationale:** Removing UI access is not enough; API tokens and remembered sessions must stop working when an account is deactivated.

---

## AD-067: Scanner Token Ability Enforcement

**Date:** 2026-06-21
**Status:** Accepted

Scanner validation requires `redemption.scan` plus a Sanctum token with `scanner:validate`; redemption requires `redemption.scan` plus `scanner:redeem`. Normal token-authenticated users derive hotel scope from `users.hotel_id`; they cannot provide or inherit an arbitrary tenant context.

**Rationale:** Scanner devices should receive least-privilege tokens. Separating validate and redeem abilities allows operators and integrations to issue read-only scanner tokens when needed.

---

## AD-068: Public Registration Disabled

**Date:** 2026-06-21
**Status:** Accepted

Laravel UI public registration routes are disabled through `Auth::routes(['register' => false])`. The existing registration controller remains for framework compatibility but is not route-exposed.

**Rationale:** Enterprise multi-hotel user creation must be tenant-scoped and permission-controlled. Public self-registration would bypass hotel assignment and role review.

---

## AD-069: Phase 6 Dashboard Metric Definitions

**Date:** 2026-06-21
**Status:** Accepted

Dashboard calculations use hotel-local date boundaries converted to UTC. Attendance percentage is checked-in participants divided by expected participants. Upcoming meetings use a 24-hour configurable window, and starting-soon alerts use a 60-minute configurable window.

**Rationale:** Hotel operations work by local hotel days, while the database stores timestamp values that must be compared consistently.

---

## AD-070: Phase 6 Reporting Export Strategy

**Date:** 2026-06-21
**Status:** Accepted

Excel exports use `maatwebsite/excel` 3.1.55, PDF exports use `barryvdh/laravel-dompdf` 2.2.0, and CSV exports stream UTF-8 with a BOM. Large exports are tracked in `report_exports` and generated by `ExportReportJob` into private local storage. Default sync thresholds are 1000 Excel rows, 5000 CSV rows, and 250 PDF rows.

**Rationale:** The repository had no Excel/PDF export package. Maintained Laravel-compatible packages avoid inventing binary file formats and keep Phase 6 aligned with the specification.

---

## AD-071: Phase 6 Consumption And Utilization Rules

**Date:** 2026-06-21
**Status:** Accepted

Package consumption uses participant entitlement totals, active redeemed quantities, remaining quantity, and redeemed divided by expected percentage. `SUCCESS` and `OVERRIDDEN` redemptions count as consumed; `REVERSED` does not. Room utilization uses scheduled reserved hours over a default 24 operational hours per day unless a future hotel setting supplies business hours.

**Rationale:** Entitlements are the authoritative consumption source after Phase 4. Hotel operating hours are not configured yet, so the denominator is documented instead of silently inventing business hours.

---

## AD-072: Phase 6 Export Security And Notification Decision

**Date:** 2026-06-21
**Status:** Accepted

Report downloads require `report.export`, completed status, unexpired records, and requester ownership unless the user is a super admin. Files are kept on the private local disk under non-public paths and expire after seven days. Notifications are deferred; export availability is shown in the Export Center.

**Rationale:** Phase 6 requires secure downloads and progress visibility, but not a new notification subsystem.

---

## AD-073: Booking-Centered Meeting QR Issuance

**Date:** 2026-06-21
**Status:** Accepted

Canonical booking create/edit can capture the primary meeting schedule, room, package, quota, source, and client in one step-style workflow. Booking numbers are generated server-side when left blank. Confirming a booking generates the meeting registration QR through `MeetingQRService`; cancelling a booking cancels linked meetings and revokes active meeting QR tokens.

**Rationale:** Hotel users expect booking creation to assign the operational package and room immediately, while Phase 4 QR security requires QR issuance to stay on hashed opaque meeting tokens rather than legacy predictable QR payloads. Legacy booking-only payloads remain accepted for compatibility.

---

## AD-074: Package Entitlements Are Repeatable Rows

**Date:** 2026-06-21
**Status:** Accepted

Package CRUD now treats entitlements as repeatable rows of type, quantity, and notes. Legacy single entitlement fields are still accepted and mapped into the same table.

**Rationale:** Real hotel packages such as Oria pilot packages may include more than one benefit, for example coffee breaks plus lunch. Repeatable rows align package setup with redemption entitlements without adding a separate package-template subsystem.

---

## AD-075: Participant Self-Registration Requires Identity Signal

**Date:** 2026-06-21
**Status:** Accepted

Meeting QR participant registration requires at least one of email, phone, or identity reference. Duplicate prevention still uses normalized email, normalized phone, or identity reference within the same meeting.

**Rationale:** Without at least one stable identity signal, the system cannot reliably prevent the same person from registering multiple times from the same or different devices.

---

## AD-076: Booking Wizard Uses Existing jQuery Steps Stack

**Date:** 2026-06-21
**Status:** Accepted

The canonical booking form uses the existing Bootstrap 4, jQuery, and jQuery Steps assets already loaded by the application. A booking-specific initializer is used instead of reusing the legacy meeting-schedule script.

**Rationale:** The project already ships and styles jQuery Steps for the original meeting schedule wizard. Reusing that stack fixes the booking form rendering without adding another frontend dependency or coupling canonical booking behavior to legacy meeting schedule JavaScript.

---

## AD-077: Meeting Schedule Creation Is Booking-Led

**Date:** 2026-06-21
**Status:** Accepted

Meeting schedule creation now starts from a selected booking. The canonical meeting create screen processes one booking and reuses the primary meeting generated during booking creation. The legacy meeting-schedule add route redirects to the canonical booking-led flow.

**Rationale:** Booking creation now captures package, room, schedule, and quota. Keeping a second full meeting-schedule wizard would duplicate the same operational inputs and increase the risk of mismatched booking and meeting data.

---

## AD-078: Legacy Tables Are Compatibility Views

**Date:** 2026-06-21
**Status:** Accepted

Legacy same-purpose tables `m_meeting_rooms`, `m_client`, `m_packages`, `r_room_status`, `trx_meeting_schedule`, and `trx_meeting_attendance` are consolidated into canonical Phase 3 tables. The legacy names remain as PostgreSQL views with `INSTEAD OF` triggers so old Eloquent models and compatibility screens continue to work while canonical tables hold the data.

**Rationale:** Dropping the names outright would break active legacy controllers, tests, QR detail relationships, and public attendance flows. Views remove duplicate storage and resource usage while preserving backward compatibility during the remaining legacy route retirement.

---

## AD-079: Meeting Attendance Operations Use Canonical Meetings

**Date:** 2026-06-22
**Status:** Accepted

The front-end Meeting Attendance menu opens the canonical `/meetings` operations list for GM, Hotel Admin, and Front Office users. The legacy attendance list remains available for compatibility, but status changes, booking processing, and attendance operation edits are handled through canonical meeting events.

**Rationale:** Booking creation now owns package, room, and schedule details, so attendance operations need to work from `meeting_events` instead of the old `transaction/meeting-attendance` list. Keeping the legacy list as a compatibility view avoids breaking old attendance lookups while making the active workflow clear.

---

## AD-080: QR Documents Are Branded PDF Tickets

**Date:** 2026-06-22
**Status:** Accepted

Generated meeting attendance QR and participant QR outputs are printable PDF tickets instead of bare SVG/PNG image files. The QR payload remains the secure opaque URL or credential token, while the PDF includes human-readable hotel, client, meeting schedule, room, booking, participant, validity, and hotel-logo context.

**Rationale:** Operators and participants need a printable document that can be verified visually before scanning. Keeping the QR payload opaque preserves the Phase 4 hash-only security model, and resolving logos from tenant settings with a default fallback avoids hard-coding ORIA branding for every hotel.

---

## AD-081: Participant QR Redemption Confirms Attendance

**Date:** 2026-06-22
**Status:** Accepted

Scanning a participant QR for an open lunch, coffee break, or other package entitlement session uses the Phase 4 redemption engine. When the redemption succeeds, the participant is moved from `REGISTERED` to `CHECKED_IN` and `checked_in_at` is set in the same database transaction. Duplicate scans for the same participant and meal session remain blocked by the redemption pre-check and PostgreSQL unique partial index.

**Rationale:** The existing participant status model uses `CHECKED_IN` as the confirmed attendance state. Reusing it avoids adding a parallel `CONFIRMED` status while making entitlement service scans update the participant’s operational status immediately and safely.

---

## AD-082: Scanner Is A Transaction Operation

**Date:** 2026-06-22
**Status:** Accepted

The QR Scanner screen is exposed in the Transaction sidebar for users with `redemption.scan`, even if they do not have the broader `Transaction` menu permission. Participant operations list filters by meeting, client, and meeting date so front-office and operations users can find attendee records by the same business context used for scanning and reporting.

**Rationale:** Scanning participant QR codes is an active operational workflow, not a hidden administration page. Scanner-only staff should be able to reach `/scanner` from the menu, and participant lookup needs meeting/client/date filters to support real service desk workflows.

---

## AD-083: Scanner Prepares Entitlement Sessions And Participant QR Reprints

**Date:** 2026-06-22
**Status:** Accepted

The scanner page auto-generates open meal/entitlement sessions for package-backed meetings that do not yet have any meal sessions. Participant QR credentials store a printable PDF path at issuance/rotation time, allowing authorized operators to reprint the active QR PDF without storing or revealing the raw token.

**Rationale:** Scanner operators need every eligible booking/meeting to appear without knowing the separate meal-session generation step. Reprint support reduces operational risk after accidental QR rotation while preserving hash-only token storage; the application stores only the generated QR document, not the raw credential token.

---

## AD-084: Tenant Settings And Subscription Metadata

**Date:** 2026-06-25
**Status:** Accepted

Tenant operational settings use the existing `hotels.settings` JSON column for logo path, contact details, QR note, and default booking source. Super-admin subscription metadata is stored under `hotels.settings.subscription` and managed from a platform-only subscription screen.

**Rationale:** The requested settings, tenant user, hotel, and subscription administration can be delivered without a schema change because the current requirement is operational metadata, not billing ledger data. Keeping subscription metadata on the hotel record preserves PostgreSQL compatibility, tenant isolation, and auditability while leaving a dedicated subscription table available for a future billing phase if invoice or lifecycle automation requirements appear.

---

## AD-085: Direct Meeting Completion Shortcut

**Date:** 2026-06-25
**Status:** Accepted

Managers may transition a meeting directly from `SCHEDULED` or `CHECKIN_OPEN` to `COMPLETED`. When this shortcut is used, missing `checkin_open_at` and `started_at` timestamps are filled before `completed_at`.

**Rationale:** Hotel operators may close a meeting after the event without first walking through every intermediate state. The shortcut keeps operational data complete while preserving terminal-state protections for `COMPLETED`, `CANCELLED`, and `NO_SHOW`.

---

## AD-086: Preserve Custom SPA Shell With History-Safe Navigation

**Date:** 2026-06-26
**Status:** Accepted

The Bootstrap 4/jQuery shell continues to use the existing partial-rendering SPA approach, but navigation is now delegated to dynamically loaded links, GET filter forms are handled through the shell, browser back/forward renders without pushing extra history entries, and `/redirect` bootstrap renders replace the history state instead of leaving `/redirect` in the stack.

**Rationale:** The application is already close to production for hotel staff and does not need a frontend rewrite. Strengthening the existing shell fixes mobile and navigation usability issues with a much lower risk than introducing a new SPA framework.
