# Security

## Authentication

- Web authentication uses Laravel session auth and Laravel UI login/logout flows.
- Password hashing is Laravel-managed through the `hashed` cast and configured bcrypt/argon hashing.
- Login throttling uses Laravel's built-in username plus IP limiter; failed and successful attempts are audited.
- Public registration is disabled; inactive users are blocked globally.
- Sanctum protects `/api/v1/*`; scanner endpoints require `auth:sanctum`, tenant scope, `redemption.scan`, and token abilities.

## Tenant Isolation

- `SetTenantScope` resolves normal users from `users.hotel_id`.
- Super-admin users may operate platform-wide or select an active tenant through `tenant_hotel_id`.
- Missing or inactive tenant context fails closed for normal hotel users and selected super-admin contexts.
- Hotel-scoped models use `ScopeByHotel`; policies and Form Requests add additional same-hotel checks.

## Sessions, CSRF, And CORS

- Web mutations remain behind Laravel CSRF middleware.
- Session cookies are HTTP-only, SameSite `lax` by default, and `SESSION_SECURE_COOKIE` is environment-driven.
- CORS origins are configured through `CORS_ALLOWED_ORIGINS`; wildcard credentials are not enabled.

## Security Headers

- `Content-Security-Policy` is configured through `SECURITY_CSP`.
- `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`, and `X-Frame-Options: DENY` are global.
- HSTS is emitted only in production.
- Camera is allowed for self origin to support the scanner UI; microphone and geolocation are disabled.

## QR And Scanner Security

- Meeting and participant QR tokens are opaque random values stored only as SHA-256 hashes.
- Raw QR tokens are shown only at issuance/regeneration time and are never logged.
- Scanner validate and redeem endpoints require an authenticated, active tenant-scoped operator with `redemption.scan`.
- Scanner tokens must carry `scanner:validate` or `scanner:redeem` for the matching endpoint.
- Scanner validate and redeem use named rate limiters keyed by operator, hotel, and device identifier.

## Audit And Sensitive Logging

- Critical security events are written to `audit_logs`.
- Audit metadata is sanitized before storage.
- Passwords, QR tokens, token hashes, API secrets, authorization headers, cookies, and session IDs are redacted.
- Audit logs are read-only through normal UI and protected by `audit.view`.

## Administrative User Security

- `/users` provides tenant-scoped user administration for users with `user.manage`.
- Hotel admins can manage only users in their active hotel and cannot assign platform roles.
- Protected roles are mediated by `RoleAuthority`; the last active super-admin cannot be deactivated.
- Deactivating a user revokes personal access tokens.

## Endpoint Review

The Phase 5 endpoint-by-endpoint review is documented in `docs/ENDPOINT_SECURITY_MATRIX.md`.

## Integration API Key Foundation

- Integration keys store only a hash of the secret and a short prefix for lookup.
- Raw secrets are returned once by `IntegrationApiKeyService::create`.
- Keys support abilities, expiration, revocation, and hotel scope.
- HMAC request signing is deferred to Phase 7; Phase 5 only provides the API key foundation.
