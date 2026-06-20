# Phase 3 Legacy Migration

## Command

```bash
php artisan headcounter:migrate-phase-three-domain --dry-run
php artisan headcounter:migrate-phase-three-domain --validate-only
php artisan headcounter:migrate-phase-three-domain --resume
php artisan headcounter:migrate-phase-three-domain --batch=500
```

## Mapping

| Source | Target |
|---|---|
| `m_meeting_rooms` | `meeting_rooms` |
| `m_client` | `clients` |
| `m_packages` | `meeting_packages` + `package_entitlements` |
| `trx_meeting_schedule` | `bookings` + `meeting_events` + `meeting_package_assignments` |
| `trx_meeting_attendance` | `participants` + `meeting_attendances` |
| `users` | `users.hotel_id` |

## Default Hotel

Legacy records without hotel context are assigned to Oria Hotel Jakarta (`ORIA`). The default is deterministic and can be audited by checking canonical `hotel_id` values after migration.

## Validation Reports

The command reports:

- Source row count.
- Target row count.
- Migrated row count.
- Skipped row count.
- Failed row count.
- Duplicate count.
- Orphan count.
- Null-required-field count.
- Unmapped status count.
- Invalid foreign-key count.
- Business-key mismatch count.

## Dry Run

`--dry-run` prints reports without writing canonical tables. Use it before production execution.

## Validate Only

`--validate-only` checks current source and target data after a previous migration or seed run.

## Rerun Behavior

The migration path is idempotent. Canonical records are written through deterministic business keys such as hotel code, room code, client external ID, booking number, package code, legacy transaction number, and participant number.

## Rollback

Phase 3 does not drop legacy tables. Rollback options:

1. Restore a database snapshot.
2. Roll back the Phase 3 migration batch before production traffic.
3. Clear canonical Phase 3 tables in dependency order only after preserving a backup.

## Failure Recovery

Resolve reported duplicate, orphan, null, or unmapped status issues in source data, then rerun the command with `--dry-run` followed by `--resume`.

## Known Limitations

This command does not implement Phase 4 QR credential generation, scanner APIs, redemptions, meal sessions, or participant entitlement balances.
