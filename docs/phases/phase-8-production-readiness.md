# Phase 8 — Production Readiness

## Objective

Achieve production readiness through comprehensive testing, performance optimization, Docker deployment, documentation, monitoring, and final security review.

---

## Prerequisites

- Phases 1–7 complete: all features implemented

---

## Tasks

### 8.1 Unit Tests

Write unit tests for all business logic:

| Test | Description |
|------|-------------|
| Package entitlement calculation | Correct quantity mapping from package to entitlements |
| QR token generation and validation | Token is random, hash is stored, validation works |
| Meeting state transitions | Only allowed transitions succeed |
| Room conflict calculation | Overlap detection for all scenarios |
| Redemption eligibility | All 22-step validation checks |
| Remaining entitlement calculation | Correct math for remaining vs redeemed |
| Entitlement synchronization | Package changes preserve history and do not reduce below redeemed |
| Duplicate participant detection | Normalized email/phone/identity match |
| Meeting QR regeneration | Old token invalidated, new token valid |
| Participant QR revocation | Revoked token rejected, active token accepted |

### 8.2 Feature Tests

| Test | Description |
|------|-------------|
| Login | User can log in with valid credentials |
| Authorization | Protected endpoints require correct permission |
| Cross-hotel access prevention | Hotel A cannot access Hotel B data |
| Create meeting | Full flow: create → assign room → set package |
| Assign room | Room conflict detected and rejected |
| Update meeting | Update without self-conflict |
| Register participant | Full registration flow |
| Detect duplicate participant | Duplicate normalized email blocked |
| Enforce quota | Over-quota registration blocked |
| Generate participant QR | QR generated and hash stored |
| Regenerate participant QR | Old QR revoked, new QR valid |
| Revoke participant QR | Revoked QR rejected |
| Redeem coffee break | Successful redemption |
| Redeem lunch | Successful redemption |
| Redeem twice in same session | Rejected as ALREADY_REDEEMED |
| Redeem without entitlement | Rejected as NO_ENTITLEMENT |
| Redeem in closed session | Rejected as SESSION_NOT_OPEN |
| Redeem with expired QR | Rejected as QR_EXPIRED |
| Redeem with revoked QR | Rejected as QR_REVOKED |
| Redeem for different meeting | Rejected as WRONG_MEETING |
| Redeem across different hotel | Rejected as WRONG_HOTEL |
| Idempotent scan retry | Same idempotency key returns same result |
| Override redemption | Requires permission and reason |
| Reverse redemption | Reversal restores entitlement |
| Cancel meeting | Status transitions correctly |
| Complete meeting | Status transitions, close remaining sessions |
| Close remaining sessions | All open sessions closed on meeting completion |

### 8.3 Concurrency Test

- Simulate two simultaneous redemption requests for same participant and meal session
- Expected: exactly one SUCCESS, other returns ALREADY_REDEEMED or DUPLICATE_REQUEST
- Verify only one successful redemption record in database

### 8.4 Performance Review

- Identify N+1 queries (use Laravel Debugbar or telescope in development)
- Add eager loading for frequently accessed relationships
- Add database indexes as specified in Phase 2
- Review slow queries
- Benchmark scanner API response time (target: < 200ms)
- Benchmark dashboard page load (target: < 1s)

### 8.5 Docker Configuration

Create:

- `Dockerfile` (multi-stage build, non-root user, production optimizations)
- `docker-compose.yml` (development: app, nginx, postgres, redis, mailpit)
- `docker-compose.production.yml` (production hardened)
- `nginx.conf` (optimized for Laravel, security headers)
- Update `.env.example` with all required variables

Minimum services:
```
app       — PHP-FPM
nginx     — Reverse proxy
postgres  — Database
redis     — Cache/Queue
queue-worker — Laravel queue worker
scheduler    — Laravel scheduler (cron)
```

### 8.6 Deployment Documentation

Create `docs/DEPLOYMENT.md` with:

- Server requirements
- PHP extensions required
- PostgreSQL requirements
- Redis requirements
- Environment variables
- Build commands
- Deployment commands
- Migration steps
- Seeder rules
- Queue worker setup
- Scheduler setup
- Storage permissions
- Reverse proxy configuration
- SSL configuration
- Backup procedures
- Rollback procedures

### 8.7 Backup and Recovery Documentation

Create `docs/BACKUP_AND_RECOVERY.md` with:

- PostgreSQL backup (pg_dump)
- Backup schedule
- Retention policy
- Encrypted backup storage
- Offsite backup
- Point-in-time recovery
- File storage backup (QR codes, exports)
- Restore procedure
- Restore testing procedure
- Disaster recovery checklist
- Recommended RPO and RTO

### 8.8 Health Endpoints

Create health check routes:

- `GET /health/live` — Returns 200 if application is running
- `GET /health/ready` — Returns 200 if database and cache are connected

### 8.9 Structured Logging

- Add `request_id` to every request (middleware)
- Log: request_id, user_id, hotel_id, route, method, status, duration, error_code
- Never log: raw QR tokens, passwords, access tokens
- Use JSON log format in production
- Configure log rotation

### 8.10 Monitoring

- Monitor failed jobs
- Monitor integration failures
- Monitor high scan rejection rates
- Monitor duplicate scan attempts
- Monitor room conflicts
- Monitor slow requests
- Monitor slow queries
- Monitor queue backlog
- Monitor readiness check failures

### 8.11 Final Security Review

- No credentials hardcoded
- No raw tokens in logs
- No stack traces in production responses
- CSRF protection on all web routes
- Authorization on all protected endpoints
- Mass-assignment protection on all models
- Rate limiting on scanner and attendance endpoints
- Tenant isolation enforced at query level
- Security headers present
- HTTPS enforced in production

### 8.12 Seeders and Demo Data

Update seeders:

- Super administrator
- Hotel administrator
- Scanner operator
- Report viewer
- Demo hotel
- Several meeting rooms
- Demo clients
- Half Day package (1 coffee break, 1 lunch)
- Full Day package (2 coffee breaks, 1 lunch)
- A meeting scheduled for today
- Demo participants
- Meal sessions
- Entitlements

Document development credentials only in `.env.example` with placeholder values.

---

## Completion Checklist

- [ ] Unit tests pass (target: 80% coverage for critical business logic)
- [ ] Feature tests pass
- [ ] Concurrency test passes
- [ ] Performance review complete, bottlenecks addressed
- [ ] Docker configuration working
- [ ] Deployment documentation complete
- [ ] Backup and recovery documentation complete
- [ ] Health endpoints working
- [ ] Structured logging configured
- [ ] Monitoring configured
- [ ] Final security review passed
- [ ] All main acceptance criteria met (see master plan section 35)
- [ ] Code formatted
- [ ] No hardcoded credentials
- [ ] No placeholder implementations in core flows
- [ ] All phase completion checklists verified

---

## Exit Criteria

The project is considered complete when all main acceptance criteria from `docs/CODEX_MASTER_PLAN.md` section 35 are met:

1. Application runs on PostgreSQL
2. Existing production data has a documented migration path
3. Room double-booking is prevented
4. Administrators can create and manage meetings
5. Administrators can assign rooms
6. Administrators can configure participant quota
7. Administrators can assign meeting packages
8. Participants can register through the meeting QR
9. Participants receive unique QR credentials
10. Scanner operators can validate and redeem participant QR codes
11. Coffee-break and lunch sessions are separate
12. A participant cannot redeem the same session twice
13. A participant can only redeem included entitlements
14. Concurrent scanning is safe
15. Retry requests are idempotent
16. All critical scans and status changes are auditable
17. Cross-hotel data access is prevented
18. API responses are consistent
19. Dashboard and reports are available
20. Queue workers and scheduler jobs operate correctly
21. Critical automated tests pass
22. Deployment documentation exists
23. Backup and recovery documentation exists
24. Docker deployment is available
25. No credentials are hardcoded
26. No unresolved critical security issues remain
27. No placeholder implementation remains in core business flow
28. The project is usable by real hotel staff