# PostgreSQL Phase 2 Stabilization

**Phase:** 2 - PostgreSQL Migration  
**Date:** 2026-06-20  
**Existing database:** PostgreSQL `head_counter`  
**Target:** PostgreSQL-compatible, constrained application schema  
**Application:** Laravel 10.48.18, PHP 8.3.3 locally

## Overview

Phase 2 was initially scoped as a MySQL-to-PostgreSQL migration, but the existing local application database has been re-verified as PostgreSQL `head_counter`. The Phase 2 work is therefore treated as PostgreSQL schema stabilization: make the existing PostgreSQL database match the production-ready Phase 2 constraints, while preserving current behavior and legacy table/column names.

New enterprise domain tables, multi-hotel tenancy, scanner APIs, redemption, reporting, and Phase 3 refactors are out of scope.

The repository defaults to `pgsql`, supports clean PostgreSQL builds with `migrate:fresh`, `db:seed`, and `test`, and includes an optional `mysql_legacy` connection only as a fallback if a real MySQL source is later discovered.

## Prerequisites

- Backup or snapshot of the existing PostgreSQL `head_counter` database before applying cleanup or constraints.
- PostgreSQL database and user available for development and testing.
- PHP `pdo_pgsql`; `pdo_mysql` only for one-time import.
- Primary `.env` uses `DB_CONNECTION=pgsql`, `DB_PORT=5432`, and a PostgreSQL database.
- Optional legacy MySQL source, only if needed later, uses `LEGACY_DB_*` variables for `mysql_legacy`.

## Schema Mapping

| Area | Phase 2 result |
|---|---|
| Primary keys | Laravel `id()` / big integer IDs retained |
| Users | `username` remains unique; nullable unique `email` added |
| Timestamps | Clean builds use `timestampsTz()` / `timestampTz()` where practical |
| Package price | `m_packages.price` converted to `numeric(15,2)` |
| QR validity | `qr_valid_start` and `qr_valid_end` use timezone-aware datetime columns |
| QR relationship | `qr_detail.meeting_id` aligned to schedule `id` type |
| Room status | `AVAILABLE`, `RESERVED`, `OCCUPIED`, `CLEANING`, `MAINTENANCE`, `INACTIVE` |

## Column Rename Strategy

Phase 2 defers broad Indonesian-to-English column renames to Phase 3. Repository search confirmed these columns are referenced across controllers, models, Blade, JavaScript, tests, and route payloads. Renaming them in Phase 2 would increase behavior risk without being required for PostgreSQL compatibility.

| Current | Target | Table |
|---|---|---|
| `code_client` | `client_code` or `client_id` | `trx_meeting_schedule` |
| `tgl_start` | `start_date` | `trx_meeting_schedule` |
| `tgl_end` | `end_date` | `trx_meeting_schedule` |
| `jam_mulai` | `start_time` | `trx_meeting_schedule` |
| `jam_selesai` | `end_time` | `trx_meeting_schedule` |
| `kuota` | `expected_participants` | `trx_meeting_schedule` |
| `kd_pck` | `code` | `m_packages` |
| `kd_room` | `code` | `m_meeting_rooms` |
| `kd_status` | `code` | `r_room_status` |
| `trx_metting_number` | `meeting_number` | `trx_meeting_attendance` |
| `jabatan` | `position` | `trx_meeting_attendance` |
| `mac_address` | `ip_address` or participant fingerprint | `trx_meeting_attendance` |

## Foreign Keys And Indexes

Added foreign keys:

- `trx_meeting_schedule.code_client` -> `m_client.code`
- `trx_meeting_schedule.package` -> `m_packages.kd_pck`
- `trx_meeting_schedule.room` -> `m_meeting_rooms.kd_room`
- `trx_meeting_attendance.trx_metting_number` -> `trx_meeting_schedule.trx_number`
- `qr_detail.meeting_id` -> `trx_meeting_schedule.id`
- `m_meeting_rooms.room_availability` -> `r_room_status.kd_status`

Historical meeting, attendance, and QR relationships use restrictive deletes. Nullable package and room schedule references use null-on-delete.

Added or verified indexes:

- `users.username`, `users.email`
- `m_client.code`
- `trx_meeting_schedule.trx_number`, `code_client`, `room`, `tgl_start`
- `m_packages.kd_pck`
- `m_meeting_rooms.kd_room`
- `r_room_status.kd_status`
- `qr_detail.meeting_id`
- `trx_meeting_attendance.trx_metting_number`

## Existing PostgreSQL Audit

Use the PostgreSQL audit command:

```bash
php artisan db:audit-postgresql-phase2
```

The command reports orphan relationships, duplicate business keys, and missing Phase 2 foreign keys.

Current verified `head_counter` findings after the fresh rebuild and seed:

- Database driver: `pgsql`
- Database name: `head_counter`
- PostgreSQL version: 12.2
- Row counts are seed/development-data dependent; the Phase 2 audit validates relationship integrity and duplicate keys after rebuild.
- Duplicates: none found for usernames, client codes, transaction numbers, package codes, room codes, or status codes
- Orphans: none found
- Missing required FKs: none found
- Required QR FK present: `qr_detail_meeting_id_foreign`
- Canonical statuses present: `AVAILABLE`, `RESERVED`, `OCCUPIED`, `CLEANING`, `MAINTENANCE`, `INACTIVE`

After any future data load or manual repair, rerun:

```bash
php artisan migrate --force
php artisan db:audit-postgresql-phase2
```

## Optional MySQL Import Fallback

The import command remains available only if a real MySQL source database is later confirmed:

```bash
php artisan db:migrate-mysql-to-pgsql --dry-run
php artisan db:migrate-mysql-to-pgsql
```

Options: `--source=mysql_legacy`, `--target=pgsql`, `--chunk=500`, `--dry-run`.

The command copies users, permissions, clients, room statuses, rooms, packages, schedules, attendance, QR details, tokens, and failed jobs. It preserves IDs and business keys, uses upserts, maps legacy room statuses, normalizes unambiguous package prices, reports invalid prices, reports duplicates and orphans, and resets PostgreSQL sequences.

## Migration Order

1. Permission tables.
2. Users.
3. Clients.
4. Room statuses.
5. Meeting rooms.
6. Packages.
7. Meeting schedules.
8. Attendance.
9. QR details.
10. Permission/user pivots and token/job support tables.

## Validation Queries

Row counts:

```sql
select count(*) from users;
select count(*) from m_client;
select count(*) from r_room_status;
select count(*) from m_meeting_rooms;
select count(*) from m_packages;
select count(*) from trx_meeting_schedule;
select count(*) from trx_meeting_attendance;
select count(*) from qr_detail;
```

Duplicate business keys:

```sql
select username, count(*) from users group by username having count(*) > 1;
select code, count(*) from m_client group by code having count(*) > 1;
select trx_number, count(*) from trx_meeting_schedule group by trx_number having count(*) > 1;
select kd_pck, count(*) from m_packages group by kd_pck having count(*) > 1;
select kd_room, count(*) from m_meeting_rooms group by kd_room having count(*) > 1;
select kd_status, count(*) from r_room_status group by kd_status having count(*) > 1;
```

Orphan checks:

```sql
select count(*) from trx_meeting_schedule s left join m_client c on c.code = s.code_client where c.code is null;
select count(*) from trx_meeting_schedule s left join m_packages p on p.kd_pck = s.package where s.package is not null and p.kd_pck is null;
select count(*) from trx_meeting_schedule s left join m_meeting_rooms r on r.kd_room = s.room where s.room is not null and r.kd_room is null;
select count(*) from trx_meeting_attendance a left join trx_meeting_schedule s on s.trx_number = a.trx_metting_number where s.trx_number is null;
select count(*) from qr_detail q left join trx_meeting_schedule s on s.id = q.meeting_id where s.id is null;
select count(*) from m_meeting_rooms r left join r_room_status s on s.kd_status = r.room_availability where s.kd_status is null;
```

## Commands

Clean PostgreSQL build:

```bash
php artisan optimize:clear
php artisan migrate:fresh
php artisan db:seed
php artisan test
./vendor/bin/pint
```

Existing-data import:

```bash
php artisan migrate --force
php artisan db:audit-postgresql-phase2
```

## Rollback Plan

Keep a PostgreSQL backup/snapshot until validation is complete. If validation fails before constraint enforcement, restore the snapshot or fix reported data issues. If validation fails after deployment, enable maintenance mode, roll back code/config, restore the database snapshot if needed, clear Laravel caches, restart workers, and reopen traffic only after smoke tests pass. Preserve QR image storage separately; database rollback does not recreate deleted files.

## Production Cutover

1. Schedule maintenance.
2. Take final PostgreSQL backup/snapshot.
3. Stop writes and queue workers.
4. Deploy Phase 2 code.
5. Run PostgreSQL migrations.
6. Run `php artisan db:audit-postgresql-phase2` and resolve blockers.
7. Confirm all required foreign keys are present.
8. Validate counts, keys, relationships, types, and smoke workflows.
9. Switch `DB_CONNECTION=pgsql`.
10. Run `php artisan optimize:clear`.
11. Restart PHP and workers.
12. Reopen traffic.

## Known Limitations

- Column renames are deferred to Phase 3.
- `count_qr` remains the legacy generic QR multiplier; entitlement modeling is deferred to Phase 4.
- `mac_address` stores a participant fingerprint; rename is deferred.
- PostgreSQL-specific SQL remains in the compatibility migration and sequence reset command because Laravel schema builder cannot express those conversions safely.
