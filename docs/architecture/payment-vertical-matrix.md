# Payment vertical matrix

// Marketing America Corp. Oleksandr Tishchenko

## Goal

This matrix defines the business verticals that must be proven for the Payment component to be considered technically reliable.

## V1. Create payment

### Scope

- request DTO
- validation
- controller endpoint
- service orchestration
- repository persistence
- response contract

### Must prove

- who can create a payment
- required and forbidden input fields
- initial status
- persistence of canonical fields
- documented response shape

### Evidence types

- unit tests for DTO validation rules
- functional endpoint test
- fixture-backed browser/UI smoke
- OpenAPI documentation entry

## V2. Start payment

### Scope

- security / access rule
- controller
- provider guard
- aggregate mutation
- repository save
- response contract

### Must prove

- who can start
- prevention of invalid repeat start
- persistence of `providerRef`
- transition to `processing`

### Evidence types

- unit test for lifecycle guard
- functional endpoint test
- security denial test
- CLI/admin UI smoke if exposed operationally

## V3. Finalize payment

### Scope

- endpoint/controller
- provider result resolution
- aggregate synchronization
- persistence
- response contract

### Must prove

- who can finalize
- completed vs failed path
- repeat finalize behaviour
- invalid payment id/provider ref handling

## V4. Refund payment

### Scope

- access control
- service/controller/command entrypoint
- provider execution
- aggregate mutation
- persistence
- audit/event/outbox side-effects

### Must prove

- who can refund
- already-refunded handling
- provider result persistence
- resulting status consistency

## V5. Webhook → outbox → consumer

### Scope

- webhook endpoint
- signature validation
- payload normalization
- webhook log
- outbox enqueue
- consumer handling
- reconciliation / aggregate update

### Must prove

- valid signature path
- invalid signature denial path
- normalized payload contract
- consumer compatibility with outbox message
- payment state update after consumer handling

## V6. Retry → DLQ → replay

### Scope

- outbox failure handling
- attempts increment
- failed status
- DLQ move
- replay back into active outbox processing

### Must prove

- first failure does not destroy the message
- threshold transition to DLQ
- replay creates a new active work item
- replayed event can complete successfully

## V7. Projection / operational read-model

### Scope

- projection sync command
- projection rebuild command
- projection repository
- status/operational endpoint or page

### Must prove

- projection rows match payment aggregate state
- rebuild is idempotent enough for operational use
- read-side endpoints/pages reflect projection truth

## Exit condition

The component should only be considered vertically proven when each of these verticals has:

- explicit endpoint/command ownership
- security rule ownership
- tests at the appropriate layer
- fixture support where needed
- documentation entry in API/internal docs
