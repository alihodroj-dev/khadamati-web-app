# Khadamati iOS API

Citizen-facing REST API for the Khadamati mobile app.

**Base URL:** `{APP_URL}/api`

**Postman:** Import [`docs/khadamati-ios.postman_collection.json`](khadamati-ios.postman_collection.json) — full citizen API with collection variables and test scripts that auto-save `token`, `challenge_token`, and `verification_session_token`.

**In-app messaging (citizen ↔ staff):** See [`docs/ios-messaging.md`](ios-messaging.md) for flow logic, polling, read receipts, and all conversation/message endpoints.

**Headers:** `Content-Type: application/json` (use `multipart/form-data` for file uploads).

**Auth (protected routes):** `Authorization: Bearer {sanctum_token}`

---

## Response envelope

```json
{
  "success": true,
  "message": "Human-readable message",
  "errors": null,
  "data": {}
}
```

Errors: `success: false`, `errors` may contain field validation messages. Common status codes: `200`, `201`, `401`, `403`, `404`, `422`.

---

## Citizen route index

| Method | Path | Auth |
|--------|------|------|
| `POST` | `/identity/preview` | No |
| `POST` | `/register/complete` | No |
| `POST` | `/auth/social` | No |
| `POST` | `/login` | No |
| `POST` | `/login/verify-otp` | No |
| `POST` | `/login/resend-otp` | No |
| `GET` | `/service-categories` | No |
| `GET` | `/service-categories/{id}` | No |
| `GET` | `/services` | No |
| `GET` | `/services/{id}` | No |
| `GET` | `/services/{id}/feedback` | No |
| `GET` | `/offices` | No |
| `GET` | `/offices/{id}` | No |
| `GET` | `/offices/{id}/feedback` | No |
| `GET` | `/track/{trackingToken}` | No |
| `GET` | `/me` | Yes |
| `POST` | `/logout` | Yes |
| `GET` | `/profile` | Yes |
| `POST` | `/profile/complete` | Yes |
| `PATCH` | `/profile` | Yes |
| `PATCH` | `/profile/password` | Yes |
| `PATCH` | `/profile/notification-preferences` | Yes |
| `POST` | `/device-tokens` | Yes |
| `DELETE` | `/device-tokens/{id}` | Yes |
| `POST` | `/service-requests` | Yes |
| `GET` | `/my-requests` | Yes |
| `GET` | `/my-requests/{id}` | Yes |
| `PATCH` | `/my-requests/{id}/cancel` | Yes |
| `GET` | `/my-requests/{id}/certificate` | Yes |
| `GET` | `/my-requests/{id}/documents` | Yes |
| `POST` | `/my-requests/{id}/documents` | Yes |
| `POST` | `/my-requests/{id}/documents/bulk` | Yes |
| `GET` | `/my-requests/{id}/documents/{docId}/download` | Yes |
| `DELETE` | `/my-requests/{id}/documents/{docId}` | Yes |
| `GET` | `/appointments/availability` | Yes |
| `GET` | `/appointments` | Yes |
| `POST` | `/appointments` | Yes |
| `GET` | `/appointments/{id}` | Yes |
| `PATCH` | `/appointments/{id}` | Yes |
| `DELETE` | `/appointments/{id}` | Yes |
| `GET` | `/payments` | Yes |
| `POST` | `/payments` | Yes |
| `GET` | `/payments/{id}` | Yes |
| `POST` | `/payments/{id}/process` | Yes |
| `GET` | `/payments/{id}/receipt` | Yes |
| `GET` | `/feedback` | Yes |
| `POST` | `/feedback` | Yes |
| `GET` | `/feedback/{id}` | Yes |
| `PATCH` | `/feedback/{id}` | Yes |
| `DELETE` | `/feedback/{id}` | Yes |
| `GET` | `/notifications` | Yes |
| `PATCH` | `/notifications/{id}/read` | Yes |
| `PATCH` | `/notifications/read-all` | Yes |
| `GET` | `/conversations/my` | Yes |
| `GET` | `/conversations/my/{serviceRequestId}` | Yes |
| `GET` | `/conversations/{id}/messages` | Yes |
| `GET` | `/conversations/{id}/poll` | Yes |
| `POST` | `/conversations/{id}/messages` | Yes |
| `POST` | `/conversations/{id}/typing` | Yes |
| `GET` | `/conversations/unread/count` | Yes |
| `GET` | `/conversations/unread/per-conversation` | Yes |
| `PATCH` | `/conversations/messages/{messageId}/read` | Yes |
| `DELETE` | `/conversations/messages/{messageId}` | Yes |

Staff/admin routes under `/staff/*`, `/admin/*`, `/dashboard/*` are **not** for the citizen app. Messaging details: [`ios-messaging.md`](ios-messaging.md).

---

## Auth

### Flows

| Scenario | Flow |
|----------|------|
| New citizen | `POST /identity/preview` → `POST /register/complete` → token |
| Google / Apple (returning) | `POST /auth/social` → token |
| Email (returning) | `POST /login` → `POST /login/verify-otp` → token |

Social sign-in returns a token immediately (no OTP). Email sign-in always requires OTP.

Registration `auth_provider`: `google` | `apple` | `email` (with `provider_token` or `password` + `password_confirmation`).

---

### Identity preview

`POST /identity/preview` — `multipart/form-data`

| Field | Rules |
|-------|--------|
| `id_front` | required, jpg/jpeg/png/pdf, max 5120 KB |
| `id_back` | required, jpg/jpeg/png/pdf, max 5120 KB |

**Response `200`**

```json
{
  "data": {
    "verification_session_token": "64-char-token",
    "fields": [
      { "key": "first_name", "label": "First Name", "type": "text", "value": "Ali", "editable": true, "required": true }
    ]
  }
}
```

Fields: `first_name`, `last_name`, `father_name`, `mother_name`, `date_of_birth`, `national_id`. OCR uses the front image only; user may edit all fields. Session expires in 24 hours.

---

### Registration completion

`POST /register/complete` — JSON

```json
{
  "verification_session_token": "from-preview",
  "auth_provider": "email",
  "email": "user@example.com",
  "first_name": "Ali",
  "last_name": "Hodroj",
  "father_name": "Father",
  "mother_name": "Mother",
  "date_of_birth": "2001-05-10",
  "national_id": "123456789",
  "phone": "+96170000001",
  "password": "password123",
  "password_confirmation": "password123"
}
```

For Google/Apple: `auth_provider` + `provider_token` instead of password.

**Response `201`:** `data.user` (`UserResource`), `data.token`. Preview session is consumed once.

---

### Social sign-in (Google & Apple)

`POST /auth/social`

```json
{
  "provider": "google",
  "id_token": "native-id-token-from-sdk",
  "first_name": "Ali",
  "last_name": "Hodroj",
  "email": "ali@example.com"
}
```

| Field | Required | Notes |
|-------|----------|--------|
| `provider` | Yes | `google` or `apple` |
| `id_token` | Yes | Google ID token or Apple identity token |
| `first_name` | No | Used when creating a new user (Apple first sign-in) |
| `last_name` | No | Same as `first_name` |
| `email` | No | Apple: send on first sign-in if not present in the token; Google: taken from the verified token |

**Response `200`:** `data.user`, `data.token`, `data.profile_completed`. Google tokens must have a verified email. Apple supports private relay emails.

---

### Email OTP login

**Step 1 —** `POST /login`

```json
{ "email": "jane@example.com", "password": "password123" }
```

**Response `200` (no token):**

```json
{
  "data": {
    "requires_otp": true,
    "challenge_token": "64-char-token",
    "expires_at": "2026-05-17T12:00:00.000000Z"
  }
}
```

In `local`, OTP is written to Laravel logs.

**Step 2 —** `POST /login/verify-otp`

```json
{ "challenge_token": "...", "otp": "123456" }
```

**Response `200`:** `data.user`, `data.token`.

**Resend —** `POST /login/resend-otp` with `{ "challenge_token": "..." }` — same challenge token, new `expires_at`.

---

### Session

| Method | Path | Notes |
|--------|------|--------|
| `GET` | `/me` | `data.user` — `UserResource` |
| `POST` | `/logout` | Revokes current token only |

---

## Profile

| Method | Path | Body (partial OK) |
|--------|------|-------------------|
| `GET` | `/profile` | — |
| `POST` | `/profile/complete` | See below (auth required) |
| `PATCH` | `/profile` | `name`, `phone`, `national_id` (not email/password/role) |
| `PATCH` | `/profile/password` | `current_password`, `password`, `password_confirmation` |
| `PATCH` | `/profile/notification-preferences` | `push_notifications_enabled`, `email_notifications_enabled`, `sms_notifications_enabled` |

### Complete profile (citizen, after OTP / social sign-in)

`POST /profile/complete` — Bearer token required.

Does **not** accept `email`, `phone`, `password`, or `password_confirmation`. Email comes from the authenticated session; citizens sign in with OTP only.

```json
{
  "verification_session_token": "from-identity-preview",
  "first_name": "Ali",
  "last_name": "Hodroj",
  "father_name": "Salah",
  "mother_name": "Fatma Alyan",
  "date_of_birth": "2004-11-27",
  "national_id": "00073028821"
}
```

**Response `200`:** `data` is a `UserResource` including `profile_completed: true`.

---

## Device tokens

`POST /device-tokens`

```json
{ "token": "apns-device-token", "platform": "ios" }
```

`platform`: `ios` | `android` | `web`. Upsert by token — `201` on create, `200` on update. Push delivery not implemented yet.

`DELETE /device-tokens/{deviceToken}` — own tokens only.

---

## Categories & services

### Categories

`GET /service-categories` → `data.categories[]` (each includes `image_url`, `icon`)

`GET /service-categories/{id}` → `data.category` (404 if inactive)

### Services

`GET /services` — query: `office_id`, `category_id`, `search` → `data.services[]` (includes `category`, `office` when set)

`GET /services/{id}` → `data.service` with `image_url` and normalized `required_documents[]`:

```json
{
  "key": "national_id_copy",
  "label": "National ID Copy",
  "required": true,
  "accepted_types": ["jpg", "jpeg", "png", "pdf"],
  "max_size_mb": 5
}
```

Legacy string entries in the database are normalized automatically.

### Public service feedback

`GET /services/{id}/feedback` — query: `rating` (1–5). `data.feedback[]` from **completed** requests only (`rating`, `comment`, `created_at`, `citizen_name` first name only).

---

## Offices

`GET /offices` — all filters optional, combined with AND:

| Query | Description |
|-------|-------------|
| `service_id` | Offices for that active service (office-specific service → one office; global service → all active offices) |
| `category_id` | Offices with at least one active service in category |
| `search` | `name`, `address`, or `email` |
| `near_lat`, `near_lng` | **Both required** — sort nearest first; `distance_km` on offices with coordinates |

Default sort: `name`. With coordinates: distance ascending (offices without coords last).

**Response** `data.offices[]` includes `image_url`, `working_hours`, `services_count`, `average_rating`, `ratings_count` (stats from **completed** requests/feedback at that office).

`GET /offices/{id}` → `data.office` (includes `image_url`)

### Public office feedback

`GET /offices/{id}/feedback` — query: `rating`. `data.feedback[]` adds `service_name`. Completed requests at that office only; no PII beyond first name.

---

## Service requests

### Create

`POST /service-requests`

```json
{
  "service_id": 1,
  "office_id": 2,
  "citizen_notes": "Optional",
  "submitted_data": {}
}
```

`office_id` required when the service is office-specific; must match that office.

**Response `201`:** `data.service_request` with `reference_number`, `tracking_token`, `tracking_api_url`, `tracking_web_url`, `office`, `status`.

**Statuses:** `pending`, `under_review`, `requires_action`, `approved`, `rejected`, `completed`, `cancelled`.

### List / show

`GET /my-requests` — query: `status`, `service_id`, `category_id`, `from_date`, `to_date`, `search`

`GET /my-requests/{id}` includes `service`, `office`, `documents`, `requirement_documents`, `official_documents`, `appointment`, `payment`, `feedback`, `required_documents`, `missing_documents`, `timeline`.

**Timeline** (computed, no extra table):

```json
"timeline": [
  { "key": "submitted", "label": "Request Submitted", "status": "completed", "occurred_at": "2026-05-10T09:00:00.000000Z" },
  { "key": "reviewed", "label": "Under Review", "status": "pending", "occurred_at": null },
  { "key": "completed", "label": "Completed", "status": "pending", "occurred_at": null }
]
```

`staff_notes` is omitted for citizens.

### Cancel

`PATCH /my-requests/{id}/cancel` — only `pending`, `under_review`, `requires_action`.

### Certificate (placeholder)

`GET /my-requests/{id}/certificate` — owner only, `status` must be `completed`. JSON only (no PDF).

```json
{
  "data": {
    "certificate": {
      "certificate_number": "CERT-KHR-20260517-ABC123",
      "request_reference": "KHR-20260517-ABC123",
      "service_name": "Passport Renewal",
      "citizen_name": "Ali Hodroj",
      "office_name": "Beirut Main Office",
      "issued_at": "2026-05-15T14:00:00.000000Z",
      "status": "valid"
    }
  }
}
```

`certificate_number` = `CERT-` + `reference_number`. `issued_at` uses `completed_at` when set.

---

## Documents

Each document: `source` (`citizen` | `staff` | `system`), `purpose` (`requirement` | `official_response` | `certificate` | `receipt` | `other`), `status`.

| List key | Use |
|----------|-----|
| `requirement_documents` | Citizen upload checklist |
| `official_documents` | Staff/system outputs |
| `documents` | Full list |
| `missing_documents` | Requirements not yet uploaded (by key) |

Citizen uploads: `source=citizen`, `purpose=requirement`, `status=pending`. Staff official uploads: `source=staff`, `purpose=official_response`, `status=approved`.

Uploading notifies **assigned staff** (database notification, type `document_upload`). Staff official uploads notify the **citizen**.

### Endpoints

| Method | Path | Notes |
|--------|------|--------|
| `GET` | `.../documents` | `data.documents`, `data.requirement_documents`, `data.official_documents` |
| `POST` | `.../documents` | multipart: `document_type` (key or legacy label), `document` (file) |
| `POST` | `.../documents/bulk` | `documents[0][document_type]`, `documents[0][file]`, … |
| `GET` | `.../documents/{id}/download` | binary file (not JSON) |
| `DELETE` | `.../documents/{id}` | citizen requirement uploads only; not if `approved` |

Blocked when request is `completed`, `cancelled`, or `rejected`. Files: jpg, jpeg, png, pdf; max 5 MB per file (per requirement `max_size_mb` when stricter).

---

## Tracking

On each `ServiceRequestResource`:

| Field | Example |
|-------|---------|
| `tracking_token` | opaque string |
| `tracking_api_url` | `{APP_URL}/api/track/{token}` — QR / in-app polling |
| `tracking_web_url` | `{APP_URL}/track/{token}` — browser page |

### Public API

`GET /track/{trackingToken}` — no auth

```json
{
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

No citizen name, documents, payments, or staff notes.

### Public web page

`GET /track/{trackingToken}` (no `/api` prefix) — minimal HTML status page for QR links opened in Safari.

---

## Appointments

### Availability

`GET /appointments/availability?date=2026-05-17` — `date` required (`Y-m-d`)

Optional: `staff_id`, `service_request_id` (uses request office `working_hours`; must own request)

Default hours: **09:00–15:00**, **30-minute** slots.

```json
{
  "data": {
    "date": "2026-05-17",
    "slot_duration_minutes": 30,
    "working_hours": { "start": "09:00", "end": "15:00" },
    "available_times": ["09:00", "09:30"],
    "unavailable_times": ["10:00"]
  }
}
```

### CRUD

| Method | Path | Notes |
|--------|------|--------|
| `GET` | `/appointments` | Own appointments; includes `office`, `service` summaries |
| `POST` | `/appointments` | `service_request_id`, `appointment_date`, `appointment_time` (`H:i`), optional `staff_id`, `notes` |
| `GET` | `/appointments/{id}` | |
| `PATCH` | `/appointments/{id}` | Citizens: only `scheduled` appointments |
| `DELETE` | `/appointments/{id}` | Citizens: `scheduled` only |

`appointment_date`: `Y-m-d`. Duplicate slot prevention.

---

## Payments

**iOS:** use `card` or `crypto` only. See [Deprecated](#deprecated--not-for-ios).

### List

`GET /payments` — query: `status`, `payment_method`, `from_date`, `to_date` → `data` is array of `PaymentResource`.

### Create

`POST /payments`

```json
{
  "service_request_id": 12,
  "appointment_id": 5,
  "payment_method": "card",
  "amount": 5.00,
  "currency": "USD"
}
```

`amount` optional (defaults to service `base_fee`). `422` if fee ≤ 0 or pending/paid payment already exists for the request.

**Card** — pending + mock confirmation:

```json
{
  "status": "pending",
  "next_action": {
    "type": "mock_card_confirmation",
    "message": "Use /payments/42/process in sandbox"
  }
}
```

**Crypto** — pending + transfer instructions (`payment_details` also populated):

```json
{
  "status": "pending",
  "next_action": {
    "type": "crypto_transfer",
    "network": "testnet",
    "wallet_address": "0xabcdef...",
    "expires_at": "2026-05-18T12:00:00.000000Z"
  }
}
```

`next_action` is `null` when status is not `pending`. No real payment gateway — sandbox only.

### Process (mock)

`POST /payments/{id}/process` — own pending payments only

```json
{ "mock_status": "paid", "mock_message": "Sandbox success" }
```

`mock_status`: `paid` | `failed` (default `paid`). Simulates card confirmation or crypto settlement.

### Receipt

`GET /payments/{id}/receipt` — **paid** only (`422` otherwise). JSON receipt (no PDF).

```json
{
  "data": {
    "receipt_number": "RCP-20260516-000042",
    "payment_id": 42,
    "transaction_reference": "TXN-ABC123",
    "request_reference_number": "KHR-20260516-ABCDEF",
    "service_name": "Birth Certificate Request",
    "service_request_status": "completed",
    "office_name": "Beirut Main Office",
    "amount": 5.0,
    "currency": "USD",
    "payment_method": "card",
    "payment_status": "paid",
    "receipt_status": "valid",
    "paid_at": "2026-05-16T12:00:00.000000Z",
    "issued_at": "2026-05-16T12:00:00.000000Z",
    "citizen_name": "Jane Citizen",
    "citizen_national_id": "123456789"
  }
}
```

`citizen_national_id` only for the payment owner when set on their profile.

---

## Feedback

### Citizen (authenticated)

| Method | Path | Notes |
|--------|------|--------|
| `GET` | `/feedback` | Own feedback |
| `POST` | `/feedback` | `service_request_id`, `rating` (1–5), `comment` — request must be `completed`; one per request |
| `GET` | `/feedback/{id}` | |
| `PATCH` | `/feedback/{id}` | Owner only |
| `DELETE` | `/feedback/{id}` | Owner only |

### Public (see [Categories & services](#categories--services), [Offices](#offices))

`GET /services/{id}/feedback`, `GET /offices/{id}/feedback`

---

## Notifications

Database notifications only (push not wired). `GET /notifications` — query `unread_only=true`

```json
{
  "data": [
    {
      "id": "uuid",
      "type": "request_update",
      "title": "Request updated",
      "body": "Your request is under review.",
      "icon": "request",
      "deep_link": { "type": "service_request", "id": 123 },
      "read_at": null,
      "created_at": "2026-05-16T10:00:00.000000Z"
    }
  ]
}
```

| `type` | `icon` | `deep_link.type` |
|--------|--------|------------------|
| `request_update` | `request` | `service_request` |
| `document_upload` | `document` | `service_request` |
| `payment_update` | `payment` | `payment` |
| `appointment_update` | `appointment` | `appointment` |
| other | `bell` | `null` |

| Method | Path |
|--------|------|
| `PATCH` | `/notifications/{id}/read` |
| `PATCH` | `/notifications/read-all` |

Raw `data` blob from Laravel notifications is **not** exposed — use `title`, `body`, `type`, `deep_link`.

---

## Deprecated / not for iOS

Do **not** call these from the citizen app.

| Method | Path | Status |
|--------|------|--------|
| `POST` | `/register` | **Deprecated** — legacy single-step registration (name, email, password, optional `id_document`). Returns token immediately. Replaced by `/identity/preview` + `/register/complete`. |
| `POST` | `/verify-id` | **Removed** — returns 404. Use identity preview flow. |
| `POST` | `/payments` with `payment_method: "cash"` | **Not for iOS** — cash is for staff/admin desk flows. Citizens use `card` or `crypto`. |
| `PATCH` | `/payments/{id}/mark-paid` | Staff/admin — mark cash paid at desk |
| `PATCH` | `/payments/{id}/refund` | Staff/admin |
| `*` | `/staff/*`, `/admin/*`, `/dashboard/*` | Staff/admin web & API tools |

---

## Quick reference

| Needs auth | Endpoints |
|------------|-----------|
| No | Auth preview/register/social/login-OTP, categories, services, offices, service/office feedback, `GET /track/{token}` |
| Yes | Profile, device tokens, requests, documents, appointments, payments (`card`/`crypto`), feedback, notifications |

**Multipart:** `POST /identity/preview`, `POST /my-requests/{id}/documents`, `POST .../documents/bulk`

**File download:** `GET /my-requests/{id}/documents/{docId}/download`

**Mock payments:** `POST /payments` → `POST /payments/{id}/process` with `mock_status: "paid"`
