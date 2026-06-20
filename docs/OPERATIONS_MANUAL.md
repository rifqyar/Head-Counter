# Operations Manual

## Tenant Context

The active hotel appears in the top navbar. Normal hotel users see their assigned hotel. Super-admins can switch to an active hotel from the navbar dropdown or `/tenant-switch`, or reset to all hotels. Invalid and inactive hotel switches keep the previous context and show a validation message.

## Client Operations

Clients are global identities associated with one or more hotels. Normal hotel users create clients for their current hotel. Super-admins can associate a client with multiple active hotels from the client form. Booking forms only list clients associated with the active hotel.

## Meeting Room Operations

Meeting rooms belong to one hotel. Normal hotel users create rooms in their active hotel automatically. Super-admins select the hotel explicitly when no tenant context is selected. A room hotel cannot be changed after meetings exist for that room.

## QR Operations

Generate or regenerate a meeting QR from the meeting admin action. The raw URL is shown only at issuance time; the stored printable SVG can be downloaded later while the token remains hashed in the database.

## Scanner Operations

Open the scanner page, select an open meal session, choose validate or redeem mode, then use camera scanning or paste a token manually. Camera scanning requires HTTPS in production. Green means success, red means rejection, and yellow means a network failure. Use Stop camera before leaving shared devices; the page also stops camera tracks on unload.

If camera permission is denied or unsupported, use the manual token fallback. Unsupported QR payloads are rejected; the scanner never navigates to scanned URLs.

API scanner tokens must be issued to an active hotel user with `redemption.scan`. Validation tokens require `scanner:validate`; redemption tokens require `scanner:redeem`. Deactivating the user or hotel blocks scanner access, and user deactivation revokes issued personal access tokens.

## User And Role Operations

Use `/users` for tenant-scoped user administration. Hotel admins can create and manage users only inside their hotel and cannot assign platform roles. Super-admins can manage protected roles, but the final active super-admin account cannot be deactivated.

Role and permission changes are available under the legacy-compatible settings pages and are audited. Protected platform permissions are filtered from non-super-admin role workflows.

## Override Operations

Review rejected redemptions at `/redemptions`. Overrideable persisted codes are `SESSION_NOT_OPEN`, `SESSION_EXPIRED`, `NO_ENTITLEMENT`, `ALREADY_REDEEMED`, `QUOTA_EXHAUSTED`, and `MEETING_COMPLETED`. A reason is required. The original rejected row remains unchanged and the override creates a linked `OVERRIDDEN` redemption.

## Participant QR Operations

Open a participant and choose Manage QR. Generate when no active credential exists, rotate to replace a lost QR, or revoke to invalidate access. The QR image and raw token appear once after generate or rotate. If that page is lost, rotate again; old QR images cannot be reconstructed from hashes.

## Idempotency Cleanup

```bash
php artisan scanner:idempotency-cleanup --dry-run
php artisan scanner:idempotency-cleanup
```

## Concurrency Test

Run the true PostgreSQL race test with:

```bash
php artisan test tests/Feature/PhaseFourConcurrencyTest.php
```

The test launches two PHP worker processes and releases them through a file barrier so both attempt the same redemption concurrently.

The scheduler runs cleanup daily at 02:00.
