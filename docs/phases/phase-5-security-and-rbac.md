# Phase 5 — Security and RBAC

## Objective

Implement role-based access control, audit logging, rate limiting, security headers, and cross-hotel isolation enforcement. Review and harden authentication.

---

## Prerequisites

- Phase 4 complete: QR and redemption engine working

---

## Tasks

### 5.1 Roles and Permissions Setup

Define and seed the role/permission structure:

**Roles:**
- SUPER_ADMIN
- HOTEL_ADMIN
- SALES_ADMIN
- BANQUET_ADMIN
- FRONT_OFFICE
- MEETING_OPERATOR
- SCANNER_OPERATOR
- REPORT_VIEWER
- AUDITOR

**Permissions:**
```
hotel.manage, user.manage, role.manage, permission.manage
meeting_room.view, meeting_room.manage
client.view, client.manage
booking.view, booking.create, booking.update, booking.cancel
meeting.view, meeting.create, meeting.update, meeting.assign_room
meeting.start, meeting.complete, meeting.cancel
participant.view, participant.register, participant.update, participant.block
attendance.view, attendance.scan
meal_package.view, meal_package.manage
meal_session.view, meal_session.manage
redemption.view, redemption.scan, redemption.override, redemption.reverse
report.view, report.export
audit.view
integration.manage
settings.manage
```

### 5.2 Policies

Create Laravel Policies for each domain entity:

- `HotelPolicy`
- `MeetingRoomPolicy`
- `ClientPolicy`
- `BookingPolicy`
- `MeetingEventPolicy`
- `ParticipantPolicy`
- `MeetingPackagePolicy`
- `MealSessionPolicy`
- `RedemptionPolicy`
- `AuditLogPolicy`
- `ReportPolicy`
- `UserPolicy`

Each policy method must:
- Check the user's role/permission
- Enforce hotel scope (user can only access data in their hotel)
- Allow SUPER_ADMIN to bypass restrictions

### 5.3 Middleware

Create or verify middleware:

- `SetTenantScope` — resolves current hotel from authenticated user
- `EnsureHasPermission` — checks specific permission
- `EnsureHasRole` — checks specific role
- Rate limiting middleware for:
  - Attendance registration form
  - Scanner API endpoints
  - Login attempts

### 5.4 Audit Logging

Create `audit_logs` table:

- Migration: `audit_logs` (id, hotel_id, actor_type, actor_id, action, entity_type, entity_id, before_data JSONB, after_data JSONB, metadata JSONB, ip_address, user_agent, request_id, created_at)

Create `AuditLogService`:

- Log events: login, logout, booking create/update/cancel, room assignment, schedule change, meeting start/complete/cancel, participant register/update/block, QR regenerate/revoke, meal session open/close, redemption success/reject/override/reverse, package changes, entitlement sync, user changes, role/permission changes, integration failures, settings changes
- Do not log: raw QR tokens, passwords, access tokens
- Audit logs must not be editable through normal UI

### 5.5 Authentication Review

- Verify Laravel Sanctum is properly configured
- Ensure session authentication works for web
- Ensure token authentication works for API
- Implement brute-force protection (throttle login attempts)
- Verify password hashing (bcrypt or argon2)
- Secure session settings (httponly, same-site, secure flag in production)
- Review CSRF protection on all web routes

### 5.6 Security Headers

Add middleware to set response headers:

```
Content-Security-Policy
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy
Strict-Transport-Security (production)
X-Frame-Options: DENY
```

### 5.7 Input Validation Review

- Ensure every input endpoint uses Form Request validation
- Verify `$fillable` on all models (no `$guarded = []`)
- Verify mass-assignment protection
- Add rate limiting to attendance registration and scanner API

### 5.8 Cross-Hotel Isolation Tests

Write tests that verify:

- Hotel A user cannot access Hotel B meeting
- Hotel A user cannot register participants for Hotel B meeting
- Hotel A scanner cannot redeem Hotel B participant
- Hotel A admin cannot view Hotel B reports
- SUPER_ADMIN can switch hotel context
- API token scoped to correct hotel

### 5.9 Integration Authentication (Foundation)

- Set up API key or HMAC authentication structure for external integration endpoints
- Create `IntegrationApiKey` model/table (to be fully implemented in Phase 7)
- Ensure scanner API requires authenticated operator

---

## Completion Checklist

- [ ] All roles and permissions seeded
- [ ] All policies created and registered
- [ ] Tenant scope middleware working
- [ ] Rate limiting on scanner and attendance endpoints
- [ ] Audit logging service created
- [ ] All critical actions logged
- [ ] No raw tokens or passwords logged
- [ ] Security headers applied
- [ ] Form Request validation on all endpoints
- [ ] `$fillable` on all models
- [ ] Cross-hotel isolation tests pass
- [ ] Login brute-force protection active
- [ ] CSRF protection verified
- [ ] Code formatted

---

## Exit Criteria

Before starting Phase 6:

1. Role-based access control is enforced on all endpoints
2. Cross-hotel data isolation is verified by automated tests
3. All critical actions are logged to audit trail
4. No security headers missing
5. Scanner API requires authenticated operator with correct permission