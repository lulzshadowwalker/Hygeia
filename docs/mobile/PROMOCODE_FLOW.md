# Promocode API Flow (Mobile)

This document defines the mobile checkout flow for promocodes and the required API contract.

Base URL: `<base-url>`

## Endpoints

### 1. Validate promocode before booking confirmation

`POST <base-url>/api/v1/promocodes/validate`

Auth:
- `Authorization: Bearer <sanctum-token>`

Request body:

```json
{
  "data": {
    "attributes": {
      "code": "SAVE20"
    },
    "relationships": {
      "service": {
        "data": { "id": 1 }
      },
      "pricing": {
        "data": { "id": 10 }
      },
      "extras": {
        "data": [{ "id": 3 }, { "id": 7 }]
      }
    }
  }
}
```

Response when valid:

```json
{
  "data": {
    "type": "promocode-validation",
    "id": "promocode-validation",
    "attributes": {
      "valid": true,
      "reason": null,
      "pricing": {
        "selectedAmount": "3000.00",
        "extrasAmount": "200.00",
        "discountAmount": "300.00",
        "totalAmount": "2900.00",
        "currency": "HUF"
      }
    },
    "includes": {
      "promocode": {
        "type": "promocode",
        "id": "4",
        "attributes": {
          "code": "SAVE20",
          "discountPercentage": "20.00",
          "maxDiscountAmount": "300.00",
          "currency": "HUF"
        }
      }
    }
  }
}
```

Response when invalid:

```json
{
  "data": {
    "type": "promocode-validation",
    "id": "promocode-validation",
    "attributes": {
      "valid": false,
      "reason": "usage_limit_reached",
      "pricing": null
    },
    "includes": {
      "promocode": null
    }
  }
}
```

### 2. Create booking with optional promocode

`POST <base-url>/api/v1/bookings`

Auth:
- `Authorization: Bearer <sanctum-token>`

Request body (promocode is optional):

```json
{
  "data": {
    "attributes": {
      "hasCleaningMaterials": true,
      "urgency": "flexible",
      "promocode": "SAVE20",
      "location": {
        "description": "Budapest, ...",
        "lat": 47.4979,
        "lng": 19.0402
      }
    },
    "relationships": {
      "service": {
        "data": { "id": 1 }
      },
      "pricing": {
        "data": { "id": 10 }
      },
      "extras": {
        "data": [{ "id": 3 }, { "id": 7 }]
      }
    }
  }
}
```

If invalid at confirmation time:
- HTTP `422`
- JSON error includes `errors[0].meta.reason`

Example:

```json
{
  "errors": [
    {
      "status": "422",
      "title": "Invalid Promocode",
      "detail": "The promocode is invalid or cannot be applied.",
      "meta": {
        "reason": "not_found"
      }
    }
  ]
}
```

## Checkout flow to implement

1. User enters promocode in checkout.
2. App calls `POST /api/v1/promocodes/validate`.
3. If `valid=true`, app updates displayed pricing using returned breakdown.
4. If `valid=false`, app reads `reason` and shows localized user message.
5. User confirms booking.
6. App sends booking request with optional `promocode`.
7. If booking returns `422` with reason enum, show localized message and keep user on checkout.

## Reason enums

Backend returns reason enums. Current set:

- `not_found`
- `inactive_period`
- `usage_limit_reached`
- `already_used`
- `booking_not_eligible`
- `unknown`

The mobile app must map these enums to localized end-user messages.

## Prompt for mobile AI coding agent

Use this exact prompt with your mobile AI coding agent:

```text
Implement promocode checkout support using Hygeia backend APIs.

Base URL: <base-url>

Requirements:
1) In checkout, provide promocode input.
2) On apply, call POST /api/v1/promocodes/validate with current booking context (service/pricing/area/extras + code).
3) If valid=true, display returned pricing breakdown (selectedAmount, extrasAmount, discountAmount, totalAmount, currency).
4) If valid=false, read reason enum and show localized user-facing message.
5) On booking confirmation, send POST /api/v1/bookings with optional promocode.
6) If booking returns 422 with errors[0].meta.reason, show localized error and keep checkout state.
7) Preserve user input and allow retry with a different code.

Important:
- The backend reason values are enums and must be handled as enum-like states in the app.
- Localize reason-based messages for end users.
- Do not hardcode language-specific strings in networking layer.
```
