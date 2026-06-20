# Phase 6 — Dashboard and Reporting

## Objective

Build the operational hotel dashboard and implement all required reports with export capabilities.

---

## Prerequisites

- Phase 5 complete: RBAC and audit logging in place

---

## Tasks

### 6.1 Operational Dashboard

Create a dashboard controller and view showing:

**Today's Overview:**
- Meetings today (count and list)
- Upcoming meetings
- Available rooms / Reserved rooms / Occupied rooms / Cleaning / Maintenance
- Expected participants
- Registered participants
- Checked-in participants
- Attendance percentage

**Redemption Summary:**
- Coffee-break entitlement count vs redeemed
- Lunch entitlement count vs redeemed
- Successful redemptions
- Rejected scans

**Alerts:**
- Meetings starting soon
- Meetings running beyond schedule
- Room-conflict warnings
- Participant over-capacity warnings
- Currently open meal sessions
- Recent scanner failures

**Filters:**
- Hotel (for multi-hotel admins)
- Date, date range
- Meeting room
- Client
- Meeting
- Status

All calculations must use the hotel timezone.

### 6.2 Meeting Report

- Booking number, client, meeting name, room, date, start/end time
- Expected participants, actual participants, attendance percentage
- Package, status
- Filter by: hotel, date range, room, client, status
- Export: Excel, CSV, PDF

### 6.3 Participant Attendance Report

- Participant name, company, contact (per authorization level)
- Registration time, check-in time
- QR status, meeting, attendance status
- Filter: hotel, meeting, date range
- Export: Excel, CSV, PDF

### 6.4 Redemption Report

- Participant, meeting, meal session, entitlement type
- Redemption time, scanner operator, device
- Result (success/rejected), rejection reason
- Override or reversal info
- Filter: hotel, meeting, date range, session, status
- Export: Excel, CSV, PDF

### 6.5 Package Consumption Report

- Meeting, package, expected quantity
- Registered participants, redeemed quantity, remaining quantity
- Consumption percentage
- Filter: hotel, date range, package
- Export: Excel, CSV, PDF

### 6.6 Room Utilization Report

- Meeting room, date range
- Total reserved hours, total occupied hours
- Utilization percentage, cancellation rate, no-show rate
- Filter: hotel, date range, room
- Export: Excel, CSV, PDF

### 6.7 Queued Exports

- Use Laravel queue for large dataset exports
- Implement `ExportReportJob` for each report type
- Track export progress
- Download link sent via notification or available in UI
- Do not load entire dataset into memory; use chunked queries or cursor

### 6.8 Report Authorization

- All reports require `report.view` permission
- Export requires `report.export` permission
- All queries are scoped to the user's hotel
- SUPER_ADMIN can query across hotels

---

## Completion Checklist

- [ ] Dashboard shows live operational data
- [ ] All 5 report types implemented with filters
- [ ] Export to Excel, CSV, PDF working
- [ ] Large exports queued
- [ ] Reports scoped by hotel
- [ ] Report authorization enforced
- [ ] Hotel timezone used for all date/time calculations
- [ ] Code formatted

---

## Exit Criteria

Before starting Phase 7:

1. Dashboard displays real data (not placeholders)
2. All reports produce correct results
3. Exports generate valid files
4. Large exports run in background
5. Reports respect hotel scope and permission