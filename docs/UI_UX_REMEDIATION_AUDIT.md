# UI/UX Remediation Audit

**Date:** 2026-06-20

## Scope

This audit covers the active Laravel, Bootstrap 4, jQuery, Ample Admin, and `core.js` partial-view application flow after Phase 4. Phase 5 was not started.

## Shared Structure

| Item | Current Standard |
|---|---|
| Main layout | `resources/views/layouts/app.blade.php` |
| Header | `resources/views/includes/navbar.blade.php` |
| Sidebar | `resources/views/includes/sidebar.blade.php` |
| Footer | `resources/views/includes/footer.blade.php` |
| AJAX loading | `public/js/core/core.js` with `.spa_route` links |
| Page title | Ample Admin `row page-titles` |
| Cards | Bootstrap `.card > .card-body` |
| Tables | `.canonical-datatable` initialized by `domain._datatable` |
| Form controls | Bootstrap 4 `.form-group`, `.form-control`, `.invalid-feedback` |
| Enhanced selects | Select2 when `.select2` is present |
| Icons | Material Design Icons and Font Awesome classes already loaded by the theme |

## Active Page Audit

| Module | Route | View | Layout used | Header | Breadcrumb | Card | Form/Table | Tenant awareness | Authorization | Known issue | Required fix |
|---|---|---|---|---|---|---|---|---|---|---|---|
| Dashboard | `/`, `/home` | `dashboard` | Main layout / partial | Present | Legacy demo label | Cards | Demo widgets | Partial | Permission middleware | Demo metrics remain | Future dashboard data pass |
| Hotels | `/hotels` | `domain.hotels.*` | Partial in main layout | Remediated | Remediated | Remediated index | DataTable | Super-admin oriented | Policy | Basic shell | Completed for index |
| Meeting Rooms | `/meeting-rooms` | `domain.meeting-rooms.*` | Partial in main layout | Remediated | Remediated | Remediated | Styled forms/table | Hotel selector/context | Policy + validation | Missing icon, weak hotel flow | Completed |
| Clients | `/clients` | `domain.clients.*` | Partial in main layout | Remediated | Remediated | Remediated | Styled forms/table | `client_hotel` association | Policy + validation | One-hotel assumption | Association compatibility implemented |
| Bookings | `/bookings` | `domain.bookings.*` | Partial in main layout | Remediated | Remediated | Remediated | Styled form/table | Client selector scoped by active hotel association | Policy + validation | Selector used `clients.hotel_id` only | Completed |
| Meeting Events | `/meetings` | `domain.meetings.*` | Partial in main layout | Index remediated | Index remediated | Index remediated | DataTable | Existing tenant scope | Policy + validation | Create/edit still basic | Remaining polish |
| Meeting Packages | `/packages` | `domain.packages.*` | Partial in main layout | Index remediated | Index remediated | Index remediated | DataTable | Existing tenant scope | Policy + validation | Forms still basic | Remaining polish |
| Participants | `/participants` | `domain.participants.*` | Partial in main layout | Index remediated | Index remediated | Index remediated | DataTable | Existing tenant scope | Policy + validation | Forms still basic | Remaining polish |
| Attendance | `transaction/meeting-attendance` | `module.Transaction.*` | Legacy partials/public views | Legacy | Legacy | Legacy | Legacy DataTables | Legacy | Permission middleware | Legacy UI retained | Future legacy deprecation pass |
| Meal Sessions | `/meal-sessions` | `domain.meal-sessions.*` | Partial in main layout | Present but basic | Partial | Cards | Basic forms | Tenant scoped | Permission middleware | Needs full shell polish | Remaining polish |
| Scanner | `/scanner` | `domain.scanner.index` | Main partial | Operational | Basic | Card/control UI | Camera/manual controls | Tenant scoped | Permission middleware | Device testing remains manual | Acceptable |
| Participant QR Admin | `/participants/{id}/qr` | `domain.participants.qr` | Partial | Operational | Basic | Cards | Forms/actions | Tenant scoped | Permission middleware | One-time token UX documented | Acceptable |
| Tenant Switcher | `/tenant-switch` | `domain.tenancy.switcher` | Partial in main layout | Remediated | Remediated | Remediated | Select2 form | Active context shown | Super-admin only | Plain/basic page, weak feedback | Completed |
| Roles and Permissions | `/setting/*` | `module.setting.*` | Legacy partials | Legacy | Legacy | Legacy | Legacy DataTables | Not hotel-scoped | Permission middleware | Legacy Ample screens | Preserved |
| Users | Sidebar placeholder | none active | N/A | N/A | N/A | N/A | N/A | N/A | Permission-gated menu | Dead link placeholder | Future Phase 5 user management |
| Settings | `/setting/*` | `module.setting.*` | Legacy partials | Legacy | Legacy | Legacy | Legacy DataTables | N/A | Permission middleware | Existing legacy style | Preserved |

## Completed Remediation

- Added shared partials for page headers, cards, validation summaries, and form actions.
- Standardized Hotels index, Meeting Rooms, Clients, Bookings, Meetings index, Packages index, Participants index, and Tenant Switcher.
- Added Select2 initialization and duplicate-submit disabling to canonical page scripts.
- Replaced the Meeting Room menu icon with loaded Font Awesome `fa fa-building`.
- Added active hotel indication in the navbar for super-admin and normal hotel users.
- Added `client_hotel` association table and backfill from `clients.hotel_id`.
- Scoped booking client selection through active `client_hotel` associations.
- Added tests for meeting-room hotel assignment, shared clients, booking client validation, and inactive tenant switch preservation.

## Remaining Limitations

- Some legacy `module/*` pages remain visually older but are preserved for compatibility.
- Dashboard still contains template/demo widgets and should be handled in the dashboard/reporting phase.
- Some create/edit/detail pages outside the remediated high-traffic set still need the shared shell applied.
- Removing client hotel associations is not exposed yet because safe removal must consider active bookings.
