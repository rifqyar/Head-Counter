# Audit Logging

## Schema

`audit_logs` contains:

- Tenant and actor: `hotel_id`, `actor_type`, `actor_id`
- Event identity: `event`, `action`
- Entity identity: `auditable_type`, `auditable_id`, `entity_type`, `entity_id`
- Payload: `before_data`, `after_data`, `metadata`
- Request context: `ip_address`, `user_agent`, `request_id`, `created_at`

Phase 4's original audit columns are retained for backward compatibility.

## Service

`App\Support\Audit\AuditLogger` is the compatibility audit service. It writes both the legacy `event` fields and the Phase 5 `action/entity` fields.

Sensitive keys are redacted recursively before storage:

- Passwords and password confirmations
- Raw QR tokens and token hashes
- Access/refresh/API secrets
- Authorization headers, cookies, and session identifiers

## Logged Events

Implemented events include login success/failure/logout, QR generation/revocation, participant QR lifecycle, public registration, entitlement synchronization, meal session state changes, redemption success/rejection/override/reversal, tenant switching, booking create/update/cancel, user create/update/activate/deactivate/role sync/token revocation, role creation/permission sync, permission create/update/delete, and integration key create/revoke.

## UI

- Route: `/audit-logs`
- Permission: `audit.view`
- Behavior: read-only, tenant-scoped, paginated, filterable by hotel, actor, action, entity, dates, and request ID.
- Delete and edit routes are not registered.

## Retention

Retention is not automated in Phase 5. Production should define retention by compliance needs before adding pruning.
