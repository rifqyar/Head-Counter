# Architectural Decisions

**Last Updated:** 2026-06-20

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
