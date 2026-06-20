# Endpoint Security Matrix

**Last Verified:** 2026-06-21

`php artisan route:list --except-vendor` reports 149 active application routes. Vendor/dev routes are excluded from this matrix.

## Conventions

| Control | Meaning |
|---|---|
| `auth` | Laravel web session authentication. |
| `auth:sanctum` | Sanctum token/session API authentication. |
| `tenant` | `SetTenantScope`; inactive users or inactive/missing hotel context fail closed. |
| `permission:*` | Spatie permission middleware. |
| `permission.web:*` | API-safe web-guard permission middleware. |
| `role:*` | Spatie role middleware. |
| `policy` | Controller/resource policy check plus hotel-scope validation. |
| `form-request` | Dedicated Form Request or Laravel auth request validation. |
| `throttle:*` | Named rate limiter. |

## Public And Auth Routes

| Endpoint Family | Methods | Route Examples | Controls | Notes |
|---|---:|---|---|---|
| Login | GET, POST | `/login` | guest, Laravel login throttle, audit | Public registration is disabled. |
| Logout | POST | `/logout` | CSRF, audit | Ends authenticated session. |
| Password reset/confirm | GET, POST | `/password/*` | Laravel auth/password brokers | Standard Laravel UI flow. |
| Public meeting attendance | GET, POST | `/attendance/meeting/{token}`, `/attendance/meeting/{token}/register` | `throttle:attendance`, token validation, form-request-style service validation | Public by design; QR token is opaque/hash-backed. |
| Legacy attendance form | GET | `/form-attendance` | `throttle:attendance` | Backward-compatible public form route. |

## Web Application Routes

| Endpoint Family | Methods | Route Examples | Required Permission/Role | Controls |
|---|---:|---|---|---|
| Dashboard/navigation | GET | `/`, `/home`, `/redirect` | authenticated user | `auth`; `/home` also requires AJAX wrapper. |
| Hotels | Resource | `/hotels/*` | `hotel.manage` | `auth`, `tenant`, `permission`, `policy`, `form-request`. |
| Meeting rooms | Resource | `/meeting-rooms/*` | `meeting_room.view|meeting_room.manage` | `auth`, `tenant`, `permission`, `policy`, `form-request`. |
| Clients | Resource | `/clients/*` | `client.view|client.manage` | `auth`, `tenant`, `permission`, `policy`, `form-request`, client-hotel association checks. |
| Bookings | Resource | `/bookings/*` | `booking.view|booking.create|booking.update|booking.cancel` | `auth`, `tenant`, `permission`, `policy`, `form-request`, audit on create/update/cancel. |
| Meetings | Resource plus transition/QR | `/meetings/*`, `/meetings/{meeting}/transition`, `/meetings/{meeting}/qr/*` | `meeting.*`, `meeting.assign_room`, `participant.qr.manage` where applicable | `auth`, `tenant`, `permission`, `policy`, `form-request`, audit. |
| Packages | Resource | `/packages/*` | `meal_package.view|meal_package.manage` | `auth`, `tenant`, `permission`, `policy`, `form-request`. |
| Participants | Resource plus QR admin | `/participants/*`, `/participants/{participant}/qr/*` | `participant.*`, `participant.qr.manage` | `auth`, `tenant`, `permission`, `policy`, `form-request`, `throttle:sensitive-admin` for QR mutations. |
| Meal sessions | Resource plus state changes | `/meal-sessions/*` | `meal_session.manage` | `auth`, `tenant`, `permission`, `policy`, `form-request`, audit. |
| Scanner UI | GET | `/scanner` | `redemption.scan` | `auth`, `tenant`, `permission`. |
| Redemptions | GET, POST | `/redemptions/*`, override, reverse | `redemption.view`, `redemption.override`, `redemption.reverse` | `auth`, `tenant`, `permission`, `policy`, `form-request`, `throttle:sensitive-admin`, audit. |
| Audit logs | GET | `/audit-logs`, `/audit-logs/{auditLog}` | `audit.view` | `auth`, `tenant`, `permission`, `policy`; no write routes registered. |
| Tenant switching | GET, POST, DELETE | `/tenant-switch` | `SUPER_ADMIN|Super Admin` | `auth`, `tenant`, `role`, `form-request`, `throttle:sensitive-admin`, audit. |
| User management | Resource plus roles/tokens/status | `/users/*`, `/users/{user}/roles`, `/users/{user}/tokens*` | `user.manage` | `auth`, `tenant`, `permission`, `policy`, `form-request`, protected-role checks, last-super-admin guard, `throttle:sensitive-admin`, audit. |

## Legacy Compatibility Routes

| Endpoint Family | Methods | Route Examples | Required Permission | Controls |
|---|---:|---|---|---|
| Legacy clients | GET, POST | `/master-data/client/*` | `Client|client.view|client.manage` | `auth`, `tenant`, AJAX middleware, permission, Form Requests for store/update. |
| Legacy meeting schedules | GET, POST | `/master-data/meeting-schedule/*` | `Meeting Schedule|meeting.view|meeting.create|meeting.update|meeting.cancel` | `auth`, `tenant`, AJAX middleware, permission, Form Requests for store/update, QR token safeguards. |
| Legacy meeting attendance admin | GET, POST | `/transaction/meeting-attendance/*` | `attendance.view|attendance.scan` for admin views; public store remains throttled | `auth`, `tenant`, AJAX middleware, permission where administrative. |
| Legacy settings permissions | GET, POST | `/setting/permission/*` | `permission.manage` | `auth`, `tenant`, AJAX middleware, permission, Form Requests, super-admin delete restriction, audit. |
| Legacy settings roles | GET, POST | `/setting/role/*` | `role.manage` | `auth`, `tenant`, AJAX middleware, permission, Form Requests, protected-role authority checks, audit. |

## API Routes

| Endpoint | Methods | Controls | Notes |
|---|---:|---|---|
| `/api/user` | GET | `auth:sanctum` | Framework-compatible authenticated user endpoint. |
| `/api/v1/user` | GET | `auth:sanctum`, `tenant` | Tenant-aware authenticated user endpoint. |
| `/api/v1/meetings` | GET | `auth:sanctum`, `tenant`, policy/model scope | Read-only meeting API. |
| `/api/v1/meetings/{meeting}` | GET | `auth:sanctum`, `tenant`, policy/model scope | Cross-hotel records resolve through scoped access. |
| `/api/v1/participants` | GET, POST | `auth:sanctum`, `tenant`, policy/model scope, form-request on write | Participant API remains hotel-scoped. |
| `/api/v1/participants/{participant}` | GET | `auth:sanctum`, `tenant`, policy/model scope | Read-only detail endpoint. |
| `/api/v1/scanner/validate` | POST | `auth:sanctum`, `tenant`, `permission.web:redemption.scan`, `token.ability:scanner:validate`, `throttle:scanner-validate`, Form Request | Rejected validation returns `422`; wrong-hotel session fails validation. |
| `/api/v1/scanner/redeem` | POST | `auth:sanctum`, `tenant`, `permission.web:redemption.scan`, `token.ability:scanner:redeem`, `throttle:scanner-redeem`, Form Request, transaction/audit | Idempotent and row-lock protected. |

## Disabled Or Removed

| Route | Status |
|---|---|
| Public registration | Disabled through `Auth::routes(['register' => false])`. |
| `/test` phpinfo route | Removed. |
| Vendor routes | Excluded from application route matrix. |
