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

### Register

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/register` |
| **Auth** | No |

**Request** (`multipart/form-data` if uploading ID document)

```json
{
  "name": "Jane Citizen",
  "email": "jane@example.com",
  "password": "password123",
  "phone": "+96170000003",
  "national_id": "CTZ-000001",
  "id_document": "<file optional>"
}
```

**Response** `201`

```json
{
  "success": true,
  "message": "Registered successfully",
  "errors": null,
  "data": {
    "user": { "id": 1, "name": "Jane Citizen", "email": "jane@example.com", "role": "citizen" },
    "token": "1|plainTextToken..."
  }
}
```

**Notes:** `id_document` — jpg, jpeg, png, pdf, max 5 MB. Store token securely.

---

### Login

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/login` |
| **Auth** | No |

**Request**

```json
{
  "email": "jane@example.com",
  "password": "password123"
}
```

**Response** `200`

```json
{
  "success": true,
  "data": {
    "user": { "id": 1, "name": "Jane Citizen", "email": "jane@example.com", "role": "citizen" },
    "token": "2|plainTextToken..."
  }
}
```

**Notes:** Returns `403` if account is inactive.

---

### Current user

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/me` |
| **Auth** | Yes |

**Response** `200` — `data.user` contains the authenticated user object.

---

### Logout

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/logout` |
| **Auth** | Yes |

**Response** `200` — `data` is `null`. Revokes the current token only.

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

### Verify ID (mock)

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/verify-id` |
| **Auth** | Yes (citizens only) |

**Request** (`multipart` if uploading document)

```json
{
  "national_id": "CTZ-000001",
  "id_document": "<file optional>"
}
```

**Response** `200`

```json
{
  "success": true,
  "data": {
    "full_name": "Jane Citizen",
    "national_id": "CTZ-000001",
    "date_of_birth": null,
    "verification_status": "verified"
  }
}
```

**Notes:** Mock verification only; updates the user's `national_id`. No external API yet.

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

**Response** `200` — `data.service`.

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
- Includes `tracking_token`, `tracking_url`, `reference_number`, `office`.

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

```
document_type: National ID copy
document: <file>
```

**Response** `201` — `data.document`.

**Notes:** jpg, jpeg, png, pdf, max 5 MB. Not allowed when request is `completed`, `cancelled`, or `rejected`.

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

**Response** `200` — citizen sees own appointments only.

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

**Response** `200`.

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

**Response** `201` — `PaymentResource`.

**Notes:**
- `amount` optional — defaults to service `base_fee`.
- `422` if fee is 0 or payment already exists (`pending`/`paid`).
- Crypto payments include mock `payment_details` (`wallet_address`, `network`).

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

**Response** `200` — `mock_status`: `paid` or `failed` (default `paid`).

**Notes:** Sandbox only. Own pending payments only.

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

### Public track by token

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/track/{trackingToken}` |
| **Auth** | No |

**Response** `200` — limited public data only.

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

**Notes:** No citizen name, documents, payments, or staff notes. Use `tracking_url` from a request for QR codes:

`{APP_URL}/track/{tracking_token}`

---

## Quick reference

| Area | Auth required |
|------|----------------|
| Register, login, categories, services, offices, service feedback, track | No |
| Everything else | Yes |

**Multipart endpoints:** `POST /register`, `POST /verify-id`, `POST /my-requests/{id}/documents`

**File download:** `GET /my-requests/{id}/documents/{id}/download`
