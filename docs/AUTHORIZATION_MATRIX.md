# Authorization Matrix

## Roles

- `SUPER_ADMIN`
- `HOTEL_ADMIN`
- `SALES_ADMIN`
- `BANQUET_ADMIN`
- `FRONT_OFFICE`
- `MEETING_OPERATOR`
- `SCANNER_OPERATOR`
- `REPORT_VIEWER`
- `AUDITOR`

Legacy roles such as `Super Admin`, `Hotel Admin`, `General Manager`, and `Front Office` remain synchronized for backward compatibility.

## Role Permissions

| Role | Permission Intent |
|---|---|
| `SUPER_ADMIN` | All platform, tenant, settings, integration, audit, QR, scanner, and report permissions. |
| `HOTEL_ADMIN` | Full operational access inside the active hotel, including user/settings foundations and audit view. |
| `SALES_ADMIN` | Clients, bookings, meeting creation/update, package view, participant view. |
| `BANQUET_ADMIN` | Meetings, room assignment, packages, meal sessions, redemption view/override/reversal. |
| `FRONT_OFFICE` | Meeting view, participant registration/update, attendance. |
| `MEETING_OPERATOR` | Meeting operation, participant handling, attendance. |
| `SCANNER_OPERATOR` | Scanner access, session view, redemption scan, redemption view. |
| `REPORT_VIEWER` | Report view/export plus read-only operational context. |
| `AUDITOR` | Audit view and read-only operational history. |

The executable matrix is in `database/seeders/RolePermissionSeeder.php`.

## Policy Rules

- Policies combine permission checks with same-hotel checks.
- `SUPER_ADMIN` can perform platform operations and may access tenant resources across hotels.
- Hotel users cannot access another hotel's resources even when IDs are guessed.
- Client authorization respects the transitional `client_hotel` association table.
- Participant QR operations use `participant.qr.manage` through `ParticipantPolicy::manageQr`.

## Route Enforcement

- Canonical web routes use `auth`, `tenant`, route permission middleware, controller authorization, and policies.
- Legacy master-data, transaction, and setting routes remain available but are now behind `tenant` and canonical/legacy permission middleware.
- Scanner API routes use `auth:sanctum`, `tenant`, web-guard `redemption.scan`, endpoint-specific token abilities, and scanner-specific throttles.
- Audit logs use `audit.view` and policy-backed tenant scope.
- User, role, permission, and token administration routes require `user.manage`, `role.manage`, or `permission.manage` and enforce protected-role boundaries through `RoleAuthority`.

## Endpoint Matrix

The expanded endpoint family review is maintained in `docs/ENDPOINT_SECURITY_MATRIX.md` and was last verified against 149 application routes on 2026-06-21.
# Phase 6 Reporting Permissions

| Permission | Grants |
|---|---|
| `report.view` | Access `/reports` and individual report pages |
| `report.export` | Request exports, view Export Center, and download completed exports |

Hotel users are constrained to their active hotel. Super admins may view all hotels or filter to one active hotel. Export downloads require ownership unless the user is a super admin.
