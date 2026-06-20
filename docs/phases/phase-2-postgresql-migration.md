# Phase 2 — PostgreSQL Migration

## Objective

Migrate the database from MySQL to PostgreSQL. Convert all MySQL-specific schema definitions, raw SQL, and Eloquent usage. Ensure data integrity and application functionality against PostgreSQL.

**Do not add new business features in this phase.** The goal is a working application on PostgreSQL with the same features as before.

---

## Prerequisites

- Phase 1 complete: application runs without critical errors, baseline tests pass
- PostgreSQL server available for development and testing
- Current MySQL database backed up

---

## Tasks

### 2.1 Schema Conversion

Convert all existing migrations to use PostgreSQL-compatible types:

| MySQL Type | PostgreSQL Type | Notes |
|------------|----------------|-------|
| `INT UNSIGNED` | `BIGINT` or `INTEGER` | Remove UNSIGNED; use CHECK constraints if needed |
| `TINYINT(1)` | `BOOLEAN` | For boolean flags |
| `VARCHAR` | `VARCHAR` or `TEXT` | Unchanged |
| `TIMESTAMP` | `TIMESTAMP WITH TIME ZONE` | Use `timestampsTz()` in migrations |
| `ENUM` | VARCHAR with CHECK or PHP enum | No native ENUM in PostgreSQL |
| `$table->string('price')` | `DECIMAL` or `NUMERIC` | Fix `m_packages.price` from string to proper numeric type |

### 2.2 Migration File Updates

Update each existing migration or create new altering migrations:

1. `create_users_table` — add email field (currently missing); cast timestamps to `timestamptz`
2. `create_m_client` — ensure proper types
3. `create_trx_meeting_schedule` — rename columns from Indonesian to English in a follow-up migration (document mapping); ensure date/time columns use proper PostgreSQL types
4. `create_trx_meeting_attendance` — fix typo column `trx_metting_number`; proper types
5. `create_packages_table` — change `price` from VARCHAR to NUMERIC; change `count_qr` to meaningful name or document it
6. `create_m_meeting_rooms` — ensure foreign key reference to room_status
7. `create_r_room_status` — ensure proper types
8. `create_generated_qr_table` (qr_detail) — ensure proper types; add foreign key constraint to meeting schedule
9. All ALTER migrations — ensure PostgreSQL compatible

### 2.3 Column Rename Strategy

Create a dedicated migration that renames Indonesian-column-name fields to English canonical names used in the target data model. Document the mapping:

| Current | Target | Table |
|---------|--------|-------|
| `code_client` | `client_code` or `client_id` (FK) | `trx_meeting_schedule` |
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
| `mac_address` | `ip_address` | `trx_meeting_attendance` |

**Important:** If any column rename breaks existing Blade views, controllers, or JavaScript, update all references simultaneously.

### 2.4 Add Foreign Key Constraints

Create a migration that adds proper foreign keys:

- `trx_meeting_schedule.code_client` → `m_client.code`
- `trx_meeting_schedule.package` → `m_packages.kd_pck`
- `trx_meeting_schedule.room` → `m_meeting_rooms.kd_room`
- `trx_meeting_attendance.trx_metting_number` → `trx_meeting_schedule.trx_number`
- `qr_detail.meeting_id` → `trx_meeting_schedule.id`
- `m_meeting_rooms.room_availability` → `r_room_status.kd_status`

### 2.5 Add Indexes

Create indexes for frequently queried columns:

- `m_client.code` (unique)
- `trx_meeting_schedule.trx_number` (unique)
- `trx_meeting_schedule.code_client`
- `trx_meeting_schedule.room`
- `trx_meeting_schedule.tgl_start`
- `qr_detail.meeting_id`
- `trx_meeting_attendance.trx_metting_number`
- `users.username` (unique — already exists)

### 2.6 Update Application Configuration

- Change `.env` to `DB_CONNECTION=pgsql`, `DB_PORT=5432`
- Update `config/database.php` if needed
- Test `php artisan migrate:fresh` against PostgreSQL
- Verify all Eloquent queries work against PostgreSQL

### 2.7 Data Migration

Create data migration scripts:

- Seed `r_room_status` with canonical statuses (AVAILABLE, RESERVED, OCCUPIED, CLEANING, MAINTENANCE, INACTIVE)
- Migrate existing string status codes to new canonical values
- Update `RoomStatusEnum` to match new canonical values
- Fix `m_packages.price` from string to numeric values

### 2.8 Fix MySQL-Specific Code

Search and replace any MySQL-specific patterns in application code:

- Raw SQL queries (check all models, controllers, helpers)
- `IFNULL`, `DATE_FORMAT`, `GROUP_CONCAT`, `FIND_IN_SET`
- `ON DUPLICATE KEY UPDATE`
- Backtick identifiers
- MySQL-only JSON functions
- Boolean values stored as integers (0/1) vs PostgreSQL `boolean`

### 2.9 Update Tests

- Run all existing tests against PostgreSQL
- Reconfigure `phpunit.xml` for PostgreSQL test database
- Ensure factories and seeders work with PostgreSQL

---

## Completion Checklist

- [ ] All migrations run cleanly on PostgreSQL (`php artisan migrate:fresh`)
- [ ] All seeders run on PostgreSQL
- [ ] All existing features work against PostgreSQL
- [ ] No MySQL-specific SQL remains in application code
- [ ] Foreign key constraints exist
- [ ] Indexes created
- [ ] Data types are PostgreSQL-native
- [ ] `docs/MYSQL_TO_POSTGRESQL_MIGRATION.md` produced
- [ ] Tests pass (`php artisan test`)
- [ ] Code formatted (`./vendor/bin/pint`)

---

## Exit Criteria

Before starting Phase 3:

1. Application runs entirely on PostgreSQL
2. All baseline tests pass on PostgreSQL
3. No MySQL-specific code remains
4. Foreign keys and indexes are in place
5. Migration documentation is complete