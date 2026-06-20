# Business Flow

## Client And Booking

```mermaid
flowchart TD
    ClientForm[Client form] --> ValidateClient[StoreClientRequest]
    ValidateClient --> CreateClient[CreateClientAction]
    CreateClient --> Client[clients]
    CreateClient --> Association[client_hotel]
    Association --> Hotel[Active hotel or selected hotels]
    BookingForm[Booking form] --> ValidateBooking[StoreBookingRequest]
    ValidateBooking --> ScopedClient{Client associated with active hotel?}
    ScopedClient -->|No| Reject[Validation error]
    ScopedClient -->|Yes| Booking[bookings]
    Client --> Booking
```

Clients can be associated with multiple hotels. Normal hotel users create or view only clients associated with their active hotel. Super-admins may associate a client with multiple active hotels.

## Meeting Creation And Room Conflict

```mermaid
flowchart TD
    RoomForm[Meeting room form] --> Tenant{Super-admin?}
    Tenant -->|Yes| SelectHotel[Select active hotel]
    Tenant -->|No| CurrentHotel[Use tenant context]
    SelectHotel --> Room[meeting_rooms.hotel_id]
    CurrentHotel --> Room
    MeetingForm[Meeting form] --> Validate[StoreMeetingEventRequest]
    Validate --> Conflict[MeetingRoomConflictService]
    Conflict -->|Overlap| Error[Validation/domain error]
    Conflict -->|No overlap| Create[CreateMeetingEventAction]
    Create --> Event[meeting_events]
    Event --> RoomStatus[Room set to RESERVED when scheduled]
```

Conflict rule:

```text
existing.start_at < requested.end_at
AND existing.end_at > requested.start_at
```

Cancelled and no-show meetings are excluded.

## Participant Registration

```mermaid
flowchart TD
    Register[Participant form] --> Validate[RegisterParticipantRequest]
    Validate --> Duplicate{Duplicate identity?}
    Duplicate -->|Yes| Reject[Reject with clear error]
    Duplicate -->|No| Create[RegisterParticipantAction]
    Create --> Participant[participants]
```

Duplicate detection uses normalized email, normalized phone, or identity reference within the same meeting.

## Meeting Lifecycle

```mermaid
stateDiagram-v2
    [*] --> DRAFT
    DRAFT --> SCHEDULED
    DRAFT --> CANCELLED
    SCHEDULED --> CHECKIN_OPEN
    SCHEDULED --> OCCUPIED
    SCHEDULED --> CANCELLED
    SCHEDULED --> NO_SHOW
    CHECKIN_OPEN --> OCCUPIED
    CHECKIN_OPEN --> CANCELLED
    CHECKIN_OPEN --> NO_SHOW
    OCCUPIED --> COMPLETED
    OCCUPIED --> CANCELLED
```

Terminal recovery from `COMPLETED`, `CANCELLED`, or `NO_SHOW` through normal forms is forbidden in Phase 3. A future administrative recovery action requires a separate permission, reason, audit trail, conflict revalidation, and room recalculation.

## Room Status Synchronization

```mermaid
flowchart TD
    Status[Meeting status changes] --> Scheduled{Scheduled or check-in open?}
    Scheduled -->|Yes| Reserved[Room RESERVED]
    Status --> Occupied{Occupied?}
    Occupied -->|Yes| RoomOccupied[Room OCCUPIED]
    Status --> Completed{Completed?}
    Completed -->|Yes| Cleaning[Room CLEANING]
    Status --> Inactive{Cancelled or no-show?}
    Inactive -->|Yes| Recalc[Recalculate other active meetings]
    Recalc --> ActiveFound{Other active meeting?}
    ActiveFound -->|Occupied| RoomOccupied
    ActiveFound -->|Scheduled/check-in| Reserved
    ActiveFound -->|No| Available[Room AVAILABLE]
```

## QR Redemption

```mermaid
flowchart TD
    MeetingQR[Meeting QR] --> Register[Public participant registration]
    Register --> ParticipantQR[Participant QR credential]
    ParticipantQR --> Validate[Scanner validation]
    Validate --> Redeem[Idempotent redemption]
    Redeem --> Entitlement[Entitlement decremented]
    Redeem --> Duplicate[Duplicate session redemption blocked]
```

Operational rejected scans with known participant, meeting, session, and tenant context are persisted as rejected redemptions when the rejection can be reviewed by staff. Approved override creates a linked `OVERRIDDEN` redemption and consumes entitlement once. Invalid QR, wrong-hotel, unresolved, authentication, authorization, and malformed attempts remain audit-only.

Participant QR administration supports generate, rotate, and revoke. Rotation is the standard lost-QR procedure because raw QR tokens are never stored and old QR images cannot be reconstructed.
