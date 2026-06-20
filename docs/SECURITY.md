# Security

- QR tokens are opaque and never include meeting IDs or participant IDs.
- Raw QR tokens are not stored in the database.
- Audit metadata stores last-four identifiers and operational context, not raw tokens.
- Scanner routes are authenticated and permission protected.
- Public attendance routes are token protected and throttled.
- Hotel scoping is enforced through tenant middleware, model scopes, request validation, and scanner action checks.

## Scanner Camera Security

- Camera scanning uses `html5-qrcode` 2.3.8 locally in the browser; camera frames are not uploaded to third-party services.
- Production camera access requires HTTPS.
- Scanned URLs are parsed only for same-origin `/scan/participant/{token}` payloads.
- Arbitrary URLs, JavaScript URLs, HTML/script-like payloads, and unsupported route shapes are rejected.
- Raw scans are not stored in browser storage.
- Camera tracks stop on Stop camera, successful decode, and page unload.

## Rejected Attempts And Override Security

- Persist rejected rows only when participant, meeting, session, and tenant context are safely resolved.
- Cross-tenant, invalid QR, unresolved, authentication, authorization, and malformed scans are audit-only.
- Overrides require `redemption.override`, a non-empty reason, same-hotel scope, and an overrideable operational rejection code.
- Original rejected rows are immutable; override writes a linked append-only row.
