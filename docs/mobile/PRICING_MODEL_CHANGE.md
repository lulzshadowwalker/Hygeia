# Pricing Model Change Guide (Mobile)

## Base URL
Use `<base-url>` for all endpoints in this document.

## What Changed
The pricing business rules changed:

1. Residential services now always use `area_range` pricing (tier-based).
2. Commercial services can use either:
- `area_range` pricing (tier-based)
- `price_per_meter` pricing (area-based)
3. Backend now exposes service-level pricing mode and minimum area:
- `pricingModel`
- `minArea`

## Service API Contract (Additive)
Service responses now include:

- `data.attributes.pricingModel`: `area_range` | `price_per_meter`
- `data.attributes.minArea`: integer or `null`

Example:

```json
{
  "data": {
    "type": "service",
    "id": "12",
    "attributes": {
      "name": {
        "en": "Office Cleaning",
        "hu": "Irodatakarítás"
      },
      "type": "commercial",
      "pricingModel": "price_per_meter",
      "pricePerMeter": "250.00",
      "minArea": 15,
      "currency": "HUF"
    },
    "includes": {
      "pricings": []
    }
  }
}
```

## Booking Create Rules (Strict Switch)
Endpoint: `POST <base-url>/api/v1/bookings`

### Rule A: `pricingModel = area_range`
- Send `data.relationships.pricing.data.id`
- Do not send `data.attributes.area`

Example:

```json
{
  "data": {
    "attributes": {
      "hasCleaningMaterials": true,
      "urgency": "flexible",
      "location": {
        "description": "Budapest, ...",
        "lat": 47.4979,
        "lng": 19.0402
      }
    },
    "relationships": {
      "service": {
        "data": { "id": 12 }
      },
      "pricing": {
        "data": { "id": 55 }
      },
      "extras": {
        "data": []
      }
    }
  }
}
```

### Rule B: `pricingModel = price_per_meter`
- Send `data.attributes.area`
- Do not send `data.relationships.pricing.data.id`
- `area` must be `>= service.minArea` when `minArea` is not null

Example:

```json
{
  "data": {
    "attributes": {
      "hasCleaningMaterials": true,
      "urgency": "flexible",
      "area": 32,
      "location": {
        "description": "Budapest, ...",
        "lat": 47.4979,
        "lng": 19.0402
      }
    },
    "relationships": {
      "service": {
        "data": { "id": 21 }
      },
      "extras": {
        "data": []
      }
    }
  }
}
```

## Immediate Strict-Switch Warning
Legacy request shapes are no longer accepted.

- Sending `area` for `area_range` services returns `422`.
- Sending `pricing.id` for `price_per_meter` services returns `422`.
- Missing required field for current mode returns `422`.

## 422 Error Handling Guidance
Handle validation errors by field path.

Common failing pointers:

- `data.relationships.pricing.data.id`
- `data.attributes.area`

Examples:

1. Missing pricing in `area_range` mode.
2. Missing area in `price_per_meter` mode.
3. `area` below `minArea` in `price_per_meter` mode.

On `422`:

1. Re-fetch service and pricing data.
2. Re-evaluate mode-specific UI state.
3. Show a mode-aware validation message.
4. Keep user inputs where possible.

## Mobile Flow
1. Load services.
2. Read `pricingModel` for selected service.
3. Render checkout input by mode:
- `area_range`: pricing picker
- `price_per_meter`: area input
4. Submit booking payload with mode-correct fields.
5. If `422`, remap UI to the correct mode and retry.

---

## Copy-Paste Prompt for Mobile AI Agent

```text
You are implementing checkout pricing integration for Hygeia mobile app.

Base URL: <base-url>

Business rules:
1. Residential services always use area-range pricing.
2. Commercial services can use either area-range or price-per-meter.
3. Service API includes:
   - pricingModel: "area_range" | "price_per_meter"
   - minArea: integer | null

Booking API strict rules:
1. If pricingModel is "area_range":
   - Send relationships.pricing.data.id
   - Do NOT send attributes.area
2. If pricingModel is "price_per_meter":
   - Send attributes.area
   - Do NOT send relationships.pricing.data.id
   - Ensure area >= minArea when minArea is present

Important:
- Backend now enforces this strictly.
- Legacy payload shapes return 422.
- Handle 422 by mapping field errors to UI, preserving user input, and showing clear validation messages.

Implement:
1. Service parsing for pricingModel and minArea.
2. Mode-driven checkout UI rendering.
3. Mode-correct booking payload builder.
4. 422 error handling for pricing mode mismatch and min-area failures.
5. Regression-safe handling for both commercial pricing modes and residential area-range flow.

Do not change backend contracts. Adapt mobile payload and UI flow to the rules above.
```
