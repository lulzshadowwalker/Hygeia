# COD + Cleaner Wallet Flow (Mobile Integration)

## Base URL
Use `<base-url>` in this document for environment-specific host values.

## Overview
This release adds Cash on Delivery (COD) settlement and cleaner wallet APIs.

1. Bookings are still created as usual.
2. Cleaner accepts and performs booking.
3. When booking is in `confirmed` state and cleaner receives cash, cleaner calls the new confirm API.
4. Backend marks booking cash as received exactly once and credits cleaner wallet.
5. Mobile wallet screen reads summary + transaction history from new wallet APIs.

## One-Time COD Confirmation Rule
`POST /api/v1/bookings/{booking}/cash-received`

- Auth: `auth:sanctum`
- Role: cleaner only
- Booking must belong to authenticated cleaner
- Booking status must be `confirmed`
- Can only be confirmed once

If the same booking is confirmed again, backend returns `409 Conflict` and wallet is not credited again.

## New/Updated API Endpoints

### 1) Confirm COD cash receipt
`POST /api/v1/bookings/{booking}/cash-received`

Request body: no attributes required.

Success: `200 OK` with updated booking resource.

Errors:
- `400` invalid booking status
- `403` unauthorized / wrong role / wrong cleaner
- `409` cash already confirmed

### 2) Wallet summary
`GET /api/v1/me/wallet`

Success: `200 OK` with wallet resource.

### 3) Wallet transactions
`GET /api/v1/me/wallet/transactions`

Success: `200 OK` with paginated transaction resource collection.

## Added Resource Fields and Types

### Booking Resource (`type = booking`)
Added under `data.attributes`:

- `paymentMethod`: `string`
- `isCashReceived`: `boolean`
- `cashReceivedAt`: `string|null` (ISO-8601 datetime)
- `cashReceivedAmount`: `string|null`
- `cashReceivedCurrency`: `string|null`
- `cashReceivedWalletTransactionId`: `number|string|null`

### Wallet Resource (`type = wallet`)
`data.attributes`:

- `balance`: `string`
- `currency`: `string`
- `transactionCount`: `number`
- `creditsTotal`: `string`
- `withdrawalsTotal`: `string`
- `platformFee`: `string`

### Wallet Transaction Resource (`type = wallet-transaction`)
`data.attributes`:

- `walletId`: `number`
- `uuid`: `string`
- `transactionType`: `string` (`deposit` or `withdraw`)
- `amount`: `string`
- `amountInt`: `number`
- `confirmed`: `boolean`
- `meta`: `object|array|null`
- `bookingId`: `number|string|null`
- `source`: `string|null`
- `createdAt`: `string|null` (ISO-8601 datetime)
- `updatedAt`: `string|null` (ISO-8601 datetime)

## Pagination Contract for Wallet Transactions
Response contains:

- `data`: transaction array
- `meta.current_page`: `number`
- `meta.last_page`: `number`
- `meta.per_page`: `number`
- `meta.total`: `number`
- `links.first|last|prev|next`: `string|null`

## Copy-Paste Prompt for Mobile AI Agent

```text
You are implementing COD settlement and wallet UI integration for the Hygeia mobile app.

Base URL: <base-url>

Backend behavior:
1. Cleaner confirms COD cash with:
   POST /api/v1/bookings/{booking}/cash-received
2. This action is one-time per booking.
3. Repeating it returns 409 Conflict and does not credit wallet again.
4. Wallet APIs:
   - GET /api/v1/me/wallet
   - GET /api/v1/me/wallet/transactions

Important resource changes:
Booking resource (data.attributes) now includes:
- paymentMethod: string
- isCashReceived: boolean
- cashReceivedAt: string|null (ISO-8601)
- cashReceivedAmount: string|null
- cashReceivedCurrency: string|null
- cashReceivedWalletTransactionId: number|string|null

Wallet resource (data.attributes):
- balance: string
- currency: string
- transactionCount: number
- creditsTotal: string
- withdrawalsTotal: string
- platformFee: string

Wallet transaction resource (data.attributes):
- walletId: number
- uuid: string
- transactionType: string
- amount: string
- amountInt: number
- confirmed: boolean
- meta: object|array|null
- bookingId: number|string|null
- source: string|null
- createdAt: string|null
- updatedAt: string|null

Implement in mobile app:
1. Booking detail action/button for cleaners: Confirm Cash Received.
2. Handle 409 gracefully as already-processed state.
3. Wallet screen showing summary values from /me/wallet.
4. Wallet history list using /me/wallet/transactions with pagination.
5. Type-safe parsing for all new fields above.

Do not change backend contracts.
Do not add alternative payload formats.
```
