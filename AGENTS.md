# Headcounter Project Instructions

## Project

An existing Laravel 10 hotel meeting headcounter application being upgraded to enterprise-grade, PostgreSQL-based, multi-hotel, production-ready.

## Key Documents

| Document | Purpose |
|----------|---------|
| `docs/CODEX_MASTER_PLAN.md` | Complete specification (do not read in full unless requested) |
| `docs/phases/phase-1-audit-and-stabilization.md` | Phase 1 tasks |
| `docs/phases/phase-2-postgresql-migration.md` | Phase 2 tasks |
| `docs/phases/phase-3-core-domain-refactor.md` | Phase 3 tasks |
| `docs/phases/phase-4-qr-and-redemption.md` | Phase 4 tasks |
| `docs/phases/phase-5-security-and-rbac.md` | Phase 5 tasks |
| `docs/phases/phase-6-dashboard-and-reporting.md` | Phase 6 tasks |
| `docs/phases/phase-7-integration-and-automation.md` | Phase 7 tasks |
| `docs/phases/phase-8-production-readiness.md` | Phase 8 tasks |
| `docs/progress/CURRENT_STATUS.md` | Current progress tracker |
| `docs/progress/DECISIONS.md` | Architectural decision records |

## Technology Stack

- Laravel 10, PHP ^8.1
- MySQL (current) -> PostgreSQL (target)
- Bootstrap 4, jQuery, Ample Admin, DataTables, Select2
- Laravel UI + Sanctum, Spatie Laravel Permission ^6.2
- SimpleSoftwareIO QR Code ~4, Yajra DataTables 10.0

## Working Method

1. Work on one phase or one bounded task at a time.
2. Read only the relevant phase document from `docs/phases/`.
3. Inspect the existing implementation before changing code.
4. Do not rebuild working modules without a documented reason.
5. Preserve backward compatibility where practical.
6. Use PostgreSQL-compatible code.
7. Keep controllers thin. Put business logic in Actions or Services.
8. Protect all hotel-scoped data from cross-tenant access.
9. Use database transactions for critical operations.
10. Never log raw QR tokens, passwords, or credentials.
11. Run relevant tests after every implementation (`php artisan test`).
12. Run code formatting after every implementation (`./vendor/bin/pint`).
13. Update `docs/progress/CURRENT_STATUS.md` after completing a task.
14. Record architectural decisions in `docs/progress/DECISIONS.md`.
15. Do not continue to the next phase unless explicitly instructed.

## Before Editing

- Inspect the relevant existing files.
- Read the relevant phase document.
- Check current migrations and tests.
- State the implementation scope.
- Avoid unrelated refactors.

## Completion Requirements

Before declaring a task complete:

- Run relevant tests (`php artisan test`).
- Run formatting (`./vendor/bin/pint`).
- Review the diff.
- List changed files.
- Document remaining risks.
- Update project progress in `docs/progress/CURRENT_STATUS.md`.