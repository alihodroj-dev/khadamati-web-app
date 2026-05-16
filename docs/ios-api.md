# Khadamati iOS API

Citizen-facing REST API for the Khadamati mobile app.

**Base URL:** `{APP_URL}/api` (e.g. `https://api.example.com/api`)

**Content-Type:** `application/json` unless noted (multipart for file uploads).

---

## Authentication

Protected routes require a Sanctum bearer token:

```http
Authorization: Bearer {token}
```

### Response envelope

All JSON endpoints use this shape:

**Success**
```json
{
  "success": true,
  "message": "Human-readable message",
  "errors": null,
  "data": { }
}
```

**Error**
```json
{
  "success": false,
  "message": "Error summary",
  "errors": {
    "field": ["Validation message"]
  },
  "data": null
}
```

Common HTTP status codes: `200`, `201`, `401`, `403`, `404`, `422`.

---

## Auth

Citizen authentication for the iOS app. Use only the routes below — not the internal/deprecated endpoints at the end of this section.

### iOS auth routes

| Method | Path | Auth | Purpose |
|--------|------|------|---------|
| `POST` | `/identity/preview` | No | Upload ID images, get OCR-prefilled form |
| `POST` | `/register/complete` | No | Finish registration after preview |
| `POST` | `/auth/google` | No | Sign in with native Google (returns token) |
| `POST` | `/auth/apple` | No | Sign in with native Apple (returns token) |
| `POST` | `/login` | No | Email/password step 1 (OTP challenge) |
| `POST` | `/login/verify-otp` | No | Email/password step 2 (token) |
| `POST` | `/login/resend-otp` | No | Resend login OTP |
| `GET` | `/me` | Yes | Current user |
| `POST` | `/logout` | Yes | Revoke current token |

### Flow overview

**New citizen (registration)**

```
POST /identity/preview  →  editable fields + verification_session_token
POST /register/complete →  user + token
```

Choose one auth method in `register/complete`:

- `auth_provider: "google"` — include `provider_token` (Google ID token)
- `auth_provider: "apple"` — include `provider_token` (Apple identity token)
- `auth_provider: "email"` — include `password` + `password_confirmation`

**Returning citizen (sign-in)**

| Method | Steps |
|--------|--------|
| Google | `POST /auth/google` → token |
| Apple | `POST /auth/apple` → token |
| Email | `POST /login` → `POST /login/verify-otp` → token (optional `POST /login/resend-otp`) |

Social sign-in does **not** use OTP. Email/password sign-in always requires OTP before a token is issued.

---

### ID preview (registration step 1)

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/identity/preview` |
| **Auth** | No |

**Request** (`multipart/form-data`)

| Field | Type | Rules |
|-------|------|-------|
| `id_front` | file | required, jpg/jpeg/png/pdf, max 5120 KB |
| `id_back` | file | required, jpg/jpeg/png/pdf, max 5120 KB |

**Response** `200`

```json
{
  "success": true,
  "message": "Identity preview generated successfully.",
  "data": {
    "verification_session_token": "64-char-token",
    "fields": [
      {
        "key": "first_name",
        "label": "First Name",
        "type": "text",
        "value": "Ali",
        "editable": true,
        "required": true
      },
      {
        "key": "last_name",
        "label": "Last Name",
        "type": "text",
        "value": "Hodroj",
        "editable": true,
        "required": true
      },
      {
        "key": "father_name",
        "label": "Father Name",
        "type": "text",
        "value": "",
        "editable": true,
        "required": true
      },
      {
        "key": "mother_name",
        "label": "Mother Name",
        "type": "text",
        "value": "",
        "editable": true,
        "required": true
      },
      {
        "key": "date_of_birth",
        "label": "Date of Birth",
        "type": "date",
        "value": null,
        "editable": true,
        "required": true
      },
      {
        "key": "national_id",
        "label": "National ID",
        "type": "text",
        "value": "",
        "editable": true,
        "required": true
      }
    ]
  }
}
```

**Notes:** OCR runs on the front ID image only. Parsed values are best-effort; let the user edit all fields. Session expires after 24 hours. Pass `verification_session_token` to `/register/complete`.

---

### Registration completion (registration step 2)

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/register/complete` |
| **Auth** | No |

**Request** (`application/json`)

```json
{
  "verification_session_token": "session-token-from-preview",
  "auth_provider": "google",
  "provider_token": "google-native-id-token",
  "email": "user@example.com",
  "first_name": "Ali",
  "last_name": "Hodroj",
  "father_name": "Father",
  "mother_name": "Mother",
  "date_of_birth": "2001-05-10",
  "national_id": "123456789",
  "phone": "+96170000001"
}
```

| Field | Required | Notes |
|-------|----------|-------|
| `verification_session_token` | Yes | From `/identity/preview` |
| `auth_provider` | Yes | `google`, `apple`, or `email` |
| `provider_token` | If google/apple | Native ID token from the mobile SDK |
| `email` | Yes | Must match provider token email when applicable |
| `first_name`, `last_name`, `father_name`, `mother_name` | Yes | |
| `date_of_birth` | Yes | `Y-m-d` |
| `national_id` | Yes | Unique |
| `phone` | No | |
| `password` | If `email` | Min 8 characters |
| `password_confirmation` | If `email` | Must match `password` |

**Response** `201`

```json
{
  "success": true,
  "message": "Registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "Ali Hodroj",
      "first_name": "Ali",
      "last_name": "Hodroj",
      "email": "user@example.com",
      "role": "citizen"
    },
    "token": "1|plainTextToken..."
  }
}
```

**Notes:** ID images are moved to permanent user storage. The preview session is marked `consumed` and cannot be reused. `name` on the user is stored as `first_name` + `last_name`.

**Email registration example** — set `auth_provider` to `"email"` and include `password` / `password_confirmation` instead of `provider_token`.

---

### Google sign-in (native)

For **returning** users who registered with Google. Returns a Sanctum token immediately (no OTP).

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/auth/google` |
| **Auth** | No |

**Request**

```json
{
  "id_token": "google-native-id-token"
}
```

**Response** `200`

```json
{
  "success": true,
  "message": "Logged in successfully",
  "data": {
    "user": { "id": 1, "email": "user@gmail.com", "role": "citizen" },
    "token": "2|plainTextToken..."
  }
}
```

**Notes:** Server verifies the Google ID token (issuer, audience, expiry, verified email). Links an existing account or creates one for first-time Google users without going through ID preview. For **new** citizens with ID verification, use registration (`/identity/preview` → `/register/complete` with `auth_provider: "google"`).

---

### Apple sign-in (native)

For **returning** users who registered with Apple. Returns a Sanctum token immediately (no OTP).

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/auth/apple` |
| **Auth** | No |

**Request**

```json
{
  "identity_token": "apple-native-identity-token",
  "full_name": "Optional Name From First Apple Login"
}
```

| Field | Required | Notes |
|-------|----------|-------|
| `identity_token` | Yes | From `ASAuthorizationAppleIDCredential` |
| `full_name` | No | Only needed when Apple omits name on later sign-ins |

**Response** `200` — same shape as Google (`data.user` + `data.token`).

**Notes:** Server verifies the Apple identity token against your app bundle ID. Supports private relay emails (`@privaterelay.appleid.com`). For **new** citizens with ID verification, use `/identity/preview` → `/register/complete` with `auth_provider: "apple"`.

---

### Email sign-in (OTP)

Password users must complete OTP before receiving a token.

#### Step 1 — `POST /login`

**Request**

```json
{
  "email": "jane@example.com",
  "password": "password123"
}
```

**Response** `200` — no token

```json
{
  "success": true,
  "message": "Verification code sent.",
  "data": {
    "requires_otp": true,
    "challenge_token": "64-char-token",
    "expires_at": "2026-05-17T12:00:00.000000Z"
  }
}
```

In `local` environment, the message hints to check Laravel logs for the OTP code.

#### Step 2 — `POST /login/verify-otp`

**Request**

```json
{
  "challenge_token": "64-char-token",
  "otp": "123456"
}
```

**Response** `200`

```json
{
  "success": true,
  "message": "Logged in successfully",
  "data": {
    "user": { "id": 1, "email": "jane@example.com", "role": "citizen" },
    "token": "2|plainTextToken..."
  }
}
```

#### Resend — `POST /login/resend-otp`

**Request**

```json
{
  "challenge_token": "64-char-token"
}
```

**Response** `200` — new `expires_at`; same `challenge_token`. OTP is logged server-side until email/SMS delivery is added.

---

### Current user

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/me` |
| **Auth** | Yes |

**Response** `200` — `data.user` is a `UserResource`.

---

### Logout

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/logout` |
| **Auth** | Yes |

**Response** `200` — `data` is `null`. Revokes the current bearer token only.

---

### Internal / deprecated (not for iOS)

These endpoints are **not** part of the citizen app. Do not call them from iOS.

| Method | Path | Status |
|--------|------|--------|
| `POST` | `/register` | Deprecated — legacy single-step registration (admin/testing) |
| `POST` | `/verify-id` | Removed — replaced by `/identity/preview` + `/register/complete` |

---

## Profile

### Get profile

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/profile` |
| **Auth** | Yes |

**Response** `200` — `data` is a `UserResource` (id, name, email, phone, national_id, role, notification flags, etc.).

---

### Update profile

| | |
|---|---|
| **Method** | `PATCH` |
| **Path** | `/profile` |
| **Auth** | Yes |

**Request** (partial)

```json
{
  "name": "Jane Citizen",
  "phone": "+96170000003",
  "national_id": "CTZ-000001"
}
```

**Response** `200` — updated `UserResource`.

**Notes:** Cannot change `email`, `password`, or `role` here.

---

### Change password

| | |
|---|---|
| **Method** | `PATCH` |
| **Path** | `/profile/password` |
| **Auth** | Yes |

**Request**

```json
{
  "current_password": "oldpassword",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Response** `200` — `data` is `null`. Other sessions are logged out.

---

### Notification preferences

| | |
|---|---|
| **Method** | `PATCH` |
| **Path** | `/profile/notification-preferences` |
| **Auth** | Yes (citizens only) |

**Request**

```json
{
  "push_notifications_enabled": true,
  "email_notifications_enabled": true,
  "sms_notifications_enabled": false
}
```

**Response** `200` — updated `UserResource`.

---

### Device tokens (push)

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/device-tokens` |
| **Auth** | Yes |

**Request**

```json
{
  "token": "apns-device-token",
  "platform": "ios"
}
```

**Response** `201` or `200` (idempotent upsert).

**Notes:** `platform`: `ios`, `android`, `web`. No push sending yet.

---

### Remove device token

| | |
|---|---|
| **Method** | `DELETE` |
| **Path** | `/device-tokens/{deviceToken}` |
| **Auth** | Yes |

**Response** `200` — own tokens only.

---

## Categories

### List categories

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/service-categories` |
| **Auth** | No |

**Response** `200`

```json
{
  "data": {
    "categories": [
      { "id": 1, "name": "Civil Records", "description": "...", "is_active": true }
    ]
  }
}
```

---

### Show category

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/service-categories/{serviceCategory}` |
| **Auth** | No |

**Response** `200` — `data.category`.

---

## Services

### List services

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/services` |
| **Auth** | No |

**Query (optional)**

| Param | Description |
|-------|-------------|
| `office_id` | Filter by office (omit to include global + all office services) |
| `category_id` | Filter by category |
| `search` | Name or description |

**Response** `200` — `data.services[]` with `category` and `office` when present.

---

### Show service

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/services/{service}` |
| **Auth** | No |

**Response** `200` — `data.service` including `required_documents[]` (each item has `key`, `label`, `required`, `accepted_types`, `max_size_mb`).

---

### Public service feedback

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/services/{service}/feedback` |
| **Auth** | No |

**Query:** `rating` (1–5, optional)

**Response** `200`

```json
{
  "data": {
    "feedback": [
      {
        "rating": 5,
        "comment": "Fast service.",
        "created_at": "2026-05-10T14:30:00.000000Z",
        "citizen_name": "Jane"
      }
    ]
  }
}
```

**Notes:** Only feedback for **completed** requests. First name only — no email or full user details.

---

## Offices

### List offices

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/offices` |
| **Auth** | No |

**Query:** `search`, `near_lat`, `near_lng` (both required together for distance sort)

**Response** `200`

```json
{
  "data": {
    "offices": [
      {
        "id": 1,
        "name": "Beirut Main Office",
        "address": "Hamra Street",
        "latitude": 33.8938,
        "longitude": 35.5018,
        "working_hours": { "mon": "08:00-16:00" },
        "is_active": true
      }
    ]
  }
}
```

---

### Show office

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/offices/{office}` |
| **Auth** | No |

**Response** `200` — `data.office`.

---

## Requests

### Create request

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/service-requests` |
| **Auth** | Yes |

**Request**

```json
{
  "service_id": 1,
  "office_id": 2,
  "citizen_notes": "Optional notes",
  "submitted_data": { "field": "value" }
}
```

**Response** `201` — `data.service_request` (`ServiceRequestResource`).

**Notes:**
- `office_id` optional for global services; required to match service if service is office-specific.
- Includes `tracking_token`, `tracking_api_url`, `tracking_web_url`, `reference_number`, `office`.
- Encode `tracking_api_url` in QR codes for in-app/API lookups; `tracking_web_url` opens the public browser page.

**Statuses:** `pending`, `under_review`, `requires_action`, `approved`, `rejected`, `completed`, `cancelled`.

---

### List my requests

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/my-requests` |
| **Auth** | Yes |

**Query (optional):** `status`, `service_id`, `category_id`, `from_date`, `to_date` (`Y-m-d`), `search` (reference or service name)

**Response** `200` — `data.service_requests[]`.

---

### Show request

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/my-requests/{serviceRequest}` |
| **Auth** | Yes |

**Response** `200` — `data.service_request` with `service`, `office`, `documents`, `appointment`, `payment`, `feedback`, `required_documents`, `missing_documents`.

`required_documents` and `missing_documents` are arrays of objects (legacy string values in the database are normalized automatically):

```json
{
  "key": "national_id_copy",
  "label": "National ID Copy",
  "required": true,
  "accepted_types": ["jpg", "jpeg", "png", "pdf"],
  "max_size_mb": 5
}
```

---

### Cancel request

| | |
|---|---|
| **Method** | `PATCH` |
| **Path** | `/my-requests/{serviceRequest}/cancel` |
| **Auth** | Yes |

**Response** `200` — updated `ServiceRequestResource`.

**Notes:** Allowed only for `pending`, `under_review`, `requires_action`.

---

## Documents

### List documents

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/my-requests/{serviceRequest}/documents` |
| **Auth** | Yes |

**Response** `200` — `data.documents[]` (`RequestDocumentResource` with `status`: `pending`, `approved`, `rejected`).

---

### Upload document

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/my-requests/{serviceRequest}/documents` |
| **Auth** | Yes |

**Request** (`multipart/form-data`)

| Field | Notes |
|-------|--------|
| `document_type` | Required document `key` (e.g. `national_id_copy`) or legacy `label` (e.g. `National ID copy`) |
| `document` | File matching `accepted_types` and `max_size_mb` from the requirement |

**Response** `201` — `data.document`. Stored `document_type` is the canonical **key** when it matches a requirement.

**Notes:** Not allowed when request is `completed`, `cancelled`, or `rejected`.

---

### Bulk upload documents

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/my-requests/{serviceRequest}/documents/bulk` |
| **Auth** | Yes |

**Request** (`multipart/form-data`)

| Field | Notes |
|-------|--------|
| `documents[0][document_type]` | Key or legacy label |
| `documents[0][file]` | File (jpg, jpeg, png, pdf; max 5 MB per file) |
| `documents[1][document_type]` | … |
| `documents[1][file]` | … |

At least one entry is required. Same ownership and status rules as single upload. Each item is validated before any file is stored.

**Response** `201`

```json
{
  "success": true,
  "message": "Documents uploaded successfully",
  "data": {
    "documents": []
  }
}
```

`data.documents[]` uses `RequestDocumentResource` (same shape as single upload).

---

### Download document

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/my-requests/{serviceRequest}/documents/{document}/download` |
| **Auth** | Yes |

**Response:** File download (not JSON). Use for certificates and staff-uploaded files.

---

### Delete document

| | |
|---|---|
| **Method** | `DELETE` |
| **Path** | `/my-requests/{serviceRequest}/documents/{document}` |
| **Auth** | Yes |

**Response** `200` — `data` is `null`.

**Notes:** Cannot delete `approved` documents.

---

## Appointments

### Availability

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/appointments/availability` |
| **Auth** | Yes |

**Query:** `date` (required, `Y-m-d`), `staff_id` (optional)

**Response** `200`

```json
{
  "data": {
    "date": "2026-05-16",
    "unavailable_times": ["09:00", "10:30"]
  }
}
```

---

### List appointments

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/appointments` |
| **Auth** | Yes |

**Response** `200` — citizen sees own appointments only. Each item is an `AppointmentResource`:

```json
{
  "id": 1,
  "status": "scheduled",
  "appointment_date": "2026-05-20",
  "appointment_time": "09:00",
  "notes": null,
  "service_request": {
    "id": 12,
    "reference_number": "KHR-20260516-ABCDEF",
    "tracking_token": "abc123token",
    "status": "under_review"
  },
  "citizen": { "id": 1, "name": "Jane Citizen", "email": "jane@example.com" },
  "staff": { "id": 3, "name": "Staff User", "email": "staff@example.com" },
  "created_at": "2026-05-16T10:00:00.000000Z",
  "updated_at": "2026-05-16T10:00:00.000000Z"
}
```

**Date formats:** `appointment_date` is `Y-m-d`; `appointment_time` is `H:i`; `created_at` / `updated_at` are ISO 8601.

---

### Book appointment

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/appointments` |
| **Auth** | Yes |

**Request**

```json
{
  "service_request_id": 12,
  "appointment_date": "2026-05-20",
  "appointment_time": "09:00",
  "staff_id": 3,
  "notes": "Optional"
}
```

**Response** `201` — `AppointmentResource`.

**Notes:** Duplicate slot prevention. `appointment_time` format `H:i`. Date must be today or later.

---

### Show appointment

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/appointments/{appointment}` |
| **Auth** | Yes |

**Response** `200` — `AppointmentResource` (same shape as list).

---

### Update appointment

| | |
|---|---|
| **Method** | `PATCH` |
| **Path** | `/appointments/{appointment}` |
| **Auth** | Yes |

**Request** (partial)

```json
{
  "appointment_date": "2026-05-21",
  "appointment_time": "10:00",
  "status": "cancelled",
  "notes": "Reschedule"
}
```

**Response** `200`.

**Notes:** Citizens may update only `scheduled` appointments.

---

### Delete appointment

| | |
|---|---|
| **Method** | `DELETE` |
| **Path** | `/appointments/{appointment}` |
| **Auth** | Yes |

**Response** `200` — scheduled appointments only.

---

## Payments

### List payments

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/payments` |
| **Auth** | Yes |

**Query (optional):** `status`, `payment_method` (`card`, `cash`, `crypto`), `from_date`, `to_date`

**Response** `200` — `PaymentResource` collection in `data`.

---

### Create payment

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/payments` |
| **Auth** | Yes |

**Request**

```json
{
  "service_request_id": 12,
  "appointment_id": 5,
  "payment_method": "card",
  "amount": 5.00,
  "currency": "USD"
}
```

**Response** `201` — `PaymentResource` with `next_action` when status is `pending`.

**Card** (`payment_method: "card"`) — mock checkout; confirm in sandbox via process endpoint:

```json
{
  "id": 42,
  "payment_method": "card",
  "status": "pending",
  "next_action": {
    "type": "mock_card_confirmation",
    "message": "Use /payments/42/process in sandbox"
  }
}
```

**Crypto** (`payment_method: "crypto"`) — mock on-chain transfer instructions:

```json
{
  "id": 43,
  "payment_method": "crypto",
  "status": "pending",
  "next_action": {
    "type": "crypto_transfer",
    "network": "testnet",
    "wallet_address": "0xabcdef...",
    "expires_at": "2026-05-18T12:00:00.000000Z"
  },
  "payment_details": {
    "provider": "mock",
    "network": "testnet",
    "wallet_address": "0xabcdef...",
    "expires_at": "2026-05-18T12:00:00.000000Z"
  }
}
```

**Cash** — no `next_action` (`null`). Staff may mark paid via admin flows.

**Notes:**
- `amount` optional — defaults to service `base_fee`.
- `422` if fee is 0 or payment already exists (`pending`/`paid`).
- No real card or crypto gateway is integrated; all flows are mocked.

---

### Show payment

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/payments/{payment}` |
| **Auth** | Yes |

**Response** `200`.

---

### Process payment (mock)

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/payments/{payment}/process` |
| **Auth** | Yes |

**Request**

```json
{
  "mock_status": "paid",
  "mock_message": "Sandbox success"
}
```

**Response** `200` — updated `PaymentResource`. `next_action` is `null` after processing (no longer `pending`).

```json
{
  "mock_status": "paid",
  "mock_message": "Sandbox success"
}
```

**Notes:** Sandbox/mock only — simulates card confirmation or crypto settlement. Own pending payments only. Default `mock_status` is `paid`.

---

### Payment receipt

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/payments/{payment}/receipt` |
| **Auth** | Yes |

**Response** `200` — JSON receipt (paid payments only).

```json
{
  "data": {
    "receipt_number": "RCP-20260516-000042",
    "payment_id": 42,
    "transaction_reference": "TXN-ABC123",
    "request_reference_number": "KHR-20260516-ABCDEF",
    "service_name": "Birth Certificate Request",
    "amount": 5.0,
    "currency": "USD",
    "payment_method": "card",
    "payment_status": "paid",
    "paid_at": "2026-05-16T12:00:00.000000Z",
    "citizen_name": "Jane Citizen"
  }
}
```

---

## Feedback

### List my feedback

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/feedback` |
| **Auth** | Yes |

**Response** `200` — own feedback only.

---

### Submit feedback

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/feedback` |
| **Auth** | Yes |

**Request**

```json
{
  "service_request_id": 12,
  "rating": 5,
  "comment": "Excellent service"
}
```

**Response** `201`.

**Notes:** Request must be `completed`. One feedback per request.

---

### Show feedback

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/feedback/{feedback}` |
| **Auth** | Yes |

**Response** `200`.

---

### Update feedback

| | |
|---|---|
| **Method** | `PATCH` |
| **Path** | `/feedback/{feedback}` |
| **Auth** | Yes |

**Request**

```json
{
  "rating": 4,
  "comment": "Updated comment"
}
```

**Response** `200` — owner only.

---

## Notifications

### List notifications

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/notifications` |
| **Auth** | Yes |

**Query:** `unread_only=true` (optional)

**Response** `200`

```json
{
  "data": [
    {
      "id": "uuid",
      "type": "payment.updated",
      "title": "Payment initiated",
      "body": "Your payment has been initiated.",
      "data": { },
      "read_at": null,
      "created_at": "2026-05-16T10:00:00.000000Z"
    }
  ]
}
```

---

### Mark one read

| | |
|---|---|
| **Method** | `PATCH` |
| **Path** | `/notifications/{notification}/read` |
| **Auth** | Yes |

**Response** `200`.

---

### Mark all read

| | |
|---|---|
| **Method** | `PATCH` |
| **Path** | `/notifications/read-all` |
| **Auth** | Yes |

**Response** `200` — `data` is `null`.

---

## Tracking

Each `ServiceRequestResource` includes:

| Field | Description |
|-------|-------------|
| `tracking_token` | Opaque token |
| `tracking_api_url` | `{APP_URL}/api/track/{tracking_token}` — use for QR codes and API polling |
| `tracking_web_url` | `{APP_URL}/track/{tracking_token}` — public browser page |

Example:

```json
{
  "tracking_token": "abc123token",
  "tracking_api_url": "https://example.com/api/track/abc123token",
  "tracking_web_url": "https://example.com/track/abc123token"
}
```

### Public track by token (API)

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/api/track/{trackingToken}` |
| **Auth** | No |

**Response** `200` — limited public data only.

```json
{
  "success": true,
  "data": {
    "reference_number": "KHR-20260516-ABCDEF",
    "status": "under_review",
    "service_name": "Birth Certificate Request",
    "submitted_at": "2026-05-16T10:00:00.000000Z",
    "reviewed_at": null,
    "completed_at": null
  }
}
```

**Notes:** No citizen name, documents, payments, or staff notes.

### Public track page (web)

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/track/{trackingToken}` |
| **Auth** | No |

Minimal HTML status page (reference, service name, status, timestamps). Same token as the API route; intended for QR codes opened in a mobile browser.

---

## Quick reference

| Area | Auth required |
|------|----------------|
| Auth (preview, register/complete, social login, login/OTP), categories, services, offices, service feedback, track | No |
| Everything else | Yes |

**iOS auth (public):** `POST /identity/preview`, `POST /register/complete`, `POST /auth/google`, `POST /auth/apple`, `POST /login`, `POST /login/verify-otp`, `POST /login/resend-otp`

**iOS auth (authenticated):** `GET /me`, `POST /logout`

**Multipart endpoints:** `POST /identity/preview`, `POST /my-requests/{id}/documents`

**File download:** `GET /my-requests/{id}/documents/{id}/download`
