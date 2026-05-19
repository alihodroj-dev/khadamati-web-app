# Khadamati iOS App Description

## 1. App Overview

Khadamati is a citizen-facing iOS application for accessing public services from government offices and municipalities. The app lets citizens verify their identity, create an account, browse offices and services, submit service requests, upload required documents, book appointments, pay service fees, track progress, download official documents, and leave feedback.

The iOS app communicates with a Laravel REST API. All successful JSON responses use the same envelope:

```json
{
  "success": true,
  "message": "Human-readable message",
  "errors": null,
  "data": {}
}
```

Errors use the same structure with `success: false`.

---

## 2. Main User Types

### 2.1 Citizen

The citizen is the primary iOS app user.

Citizens can:

- Verify identity using front and back ID card images.
- Register using email, Google, or Apple.
- Log in using social auth or email OTP.
- Browse services and offices.
- Submit service requests.
- Upload required documents.
- Track request status.
- Book appointments.
- Pay with card or crypto in the current sandbox flow.
- View request and payment history.
- Download official documents.
- View JSON certificates and receipts.
- Submit feedback.
- Receive database/in-app notifications.

### 2.2 Staff And Admin

Staff and admin routes exist in the backend, but they are not part of the citizen iOS app. The iOS app should not call `/staff/*`, `/admin/*`, or `/dashboard/*`.

---

## 3. Authentication And Registration

## 3.1 New Citizen Registration Flow

The registration flow is split into two main steps.

### Step 1: Identity Preview

Endpoint:

```http
POST /api/identity/preview
```

The citizen uploads:

- `id_front`
- `id_back`

Both files must be:

- jpg, jpeg, png, or pdf
- max 5120 KB

The backend uses OCR on the front ID image and returns a temporary verification session plus fields that the iOS app can render as a form.

Returned fields:

- `first_name`
- `last_name`
- `father_name`
- `mother_name`
- `date_of_birth`
- `national_id`

Each field includes:

- `key`
- `label`
- `type`
- `value`
- `editable`
- `required`

The user can edit the returned values before final registration.

### Step 2: Complete Registration

Endpoint:

```http
POST /api/register/complete
```

The iOS app submits:

- `verification_session_token`
- edited identity fields
- phone
- email
- auth provider
- password or social provider token

Supported auth providers:

- `email`
- `google`
- `apple`

For email registration, the app sends:

- `password`
- `password_confirmation`

For Google or Apple registration, the app sends:

- `provider_token`

The backend consumes the preview session and returns:

- `user`
- `token`

The token is a Sanctum bearer token and should be stored securely by iOS.

---

## 3.2 Returning User Login

### Social Login (Google & Apple)

Endpoint:

```http
POST /api/auth/social
```

The iOS app sends:

- `provider`: `google` or `apple`
- `id_token`: native token from the SDK
- `first_name`, `last_name`, `email`: optional; especially important on first Apple sign-in when Apple only shares name/email once

The backend verifies the token and returns:

- `user`
- `token`
- `profile_completed`

Social users do not go through OTP.

### Email Login With OTP

Email login is a two-step flow.

Step 1:

```http
POST /api/login
```

The app sends email and password. If valid, the backend returns:

- `requires_otp: true`
- `challenge_token`
- `expires_at`

No login token is returned yet.

Step 2:

```http
POST /api/login/verify-otp
```

The app sends:

- `challenge_token`
- `otp`

If valid, the backend returns:

- `user`
- `token`

OTP can be resent through:

```http
POST /api/login/resend-otp
```

---

## 4. Profile And Device Tokens

## 4.1 Profile

Authenticated citizens can manage profile data.

Endpoints:

- `GET /api/profile`
- `PATCH /api/profile`
- `PATCH /api/profile/password`
- `PATCH /api/profile/notification-preferences`

Editable profile fields:

- `name`
- `phone`
- `national_id`

The profile endpoint does not allow changing:

- email
- role
- account activation status

Notification preferences:

- `push_notifications_enabled`
- `email_notifications_enabled`
- `sms_notifications_enabled`

These preferences are stored, but real push/email/SMS delivery is not wired yet.

## 4.2 Device Tokens

Endpoint:

```http
POST /api/device-tokens
```

The app registers an APNs or push token:

```json
{
  "token": "apns-device-token",
  "platform": "ios"
}
```

Supported platforms:

- `ios`
- `android`
- `web`

Device token deletion:

```http
DELETE /api/device-tokens/{id}
```

---

## 5. Categories And Services

## 5.1 Categories

Endpoints:

- `GET /api/service-categories`
- `GET /api/service-categories/{id}`

Categories organize services, such as civil records, municipality services, tax services, health services, and education services.

Inactive categories return 404 when requested directly.

## 5.2 Services

Endpoints:

- `GET /api/services`
- `GET /api/services/{id}`
- `GET /api/services/{id}/feedback`

Service filters:

- `office_id`
- `category_id`
- `search`

Service data includes:

- category
- office, when set
- name
- description
- base fee
- estimated processing days
- required documents
- appointment requirement
- active status

Required documents are normalized into objects:

```json
{
  "key": "national_id_copy",
  "label": "National ID Copy",
  "required": true,
  "accepted_types": ["jpg", "jpeg", "png", "pdf"],
  "max_size_mb": 5
}
```

The iOS app should use `key` when uploading documents.

---

## 6. Offices

Citizens can discover offices and view public feedback.

Endpoints:

- `GET /api/offices`
- `GET /api/offices/{id}`
- `GET /api/offices/{id}/feedback`

Office filters:

- `service_id`
- `category_id`
- `search`
- `near_lat`
- `near_lng`

When both coordinates are provided, offices are sorted by distance and include `distance_km` when coordinates exist.

Office data includes:

- name
- address
- phone
- email
- latitude
- longitude
- working hours
- services count
- average rating
- ratings count
- active status

iOS is responsible for displaying these coordinates on Apple Maps or Google Maps.

---

## 7. Service Requests

## 7.1 Creating A Request

Endpoint:

```http
POST /api/service-requests
```

Body:

```json
{
  "service_id": 1,
  "office_id": 2,
  "citizen_notes": "Optional note",
  "submitted_data": {}
}
```

The backend creates:

- `reference_number`
- `tracking_token`
- `tracking_api_url`
- `tracking_web_url`
- initial status `pending`

Valid statuses:

- `pending`
- `under_review`
- `requires_action`
- `approved`
- `rejected`
- `completed`
- `cancelled`

## 7.2 Request History

Endpoint:

```http
GET /api/my-requests
```

Filters:

- `status`
- `service_id`
- `category_id`
- `from_date`
- `to_date`
- `search`

## 7.3 Request Details

Endpoint:

```http
GET /api/my-requests/{id}
```

Details include:

- service
- office
- all documents
- requirement documents
- official documents
- missing documents
- appointment
- payment
- feedback
- timeline

Staff notes are omitted for citizens.

## 7.4 Cancel Request

Endpoint:

```http
PATCH /api/my-requests/{id}/cancel
```

Only these statuses can be cancelled:

- `pending`
- `under_review`
- `requires_action`

---

## 8. Documents

Documents are attached to service requests.

Document source values:

- `citizen`
- `staff`
- `system`

Document purpose values:

- `requirement`
- `official_response`
- `certificate`
- `receipt`
- `other`

Citizen uploads become:

- `source = citizen`
- `purpose = requirement`
- `status = pending`

Staff/system outputs appear in `official_documents`.

Endpoints:

- `GET /api/my-requests/{id}/documents`
- `POST /api/my-requests/{id}/documents`
- `POST /api/my-requests/{id}/documents/bulk`
- `GET /api/my-requests/{id}/documents/{docId}/download`
- `DELETE /api/my-requests/{id}/documents/{docId}`

Single upload uses multipart:

- `document_type`
- `document`

Bulk upload uses multipart:

- `documents[0][document_type]`
- `documents[0][file]`
- `documents[1][document_type]`
- `documents[1][file]`

Allowed file types:

- jpg
- jpeg
- png
- pdf

Uploads are blocked when the request is:

- `completed`
- `cancelled`
- `rejected`

Approved documents cannot be deleted.

---

## 9. Tracking And QR Support

Every service request has public tracking URLs.

Resource fields:

- `tracking_token`
- `tracking_api_url`
- `tracking_web_url`

Public API:

```http
GET /api/track/{trackingToken}
```

Public web page:

```http
GET /track/{trackingToken}
```

The API response is public-safe and does not expose:

- citizen name
- documents
- payments
- staff notes

iOS can generate a QR code from `tracking_web_url` or `tracking_api_url`.

---

## 10. Appointments

Citizens can check availability and manage appointments.

Availability endpoint:

```http
GET /api/appointments/availability
```

Required:

- `date` in `Y-m-d`

Optional:

- `staff_id`
- `service_request_id`

Availability response includes:

- date
- slot duration
- working hours
- available times
- unavailable times

Default schedule:

- 09:00 to 15:00
- 30-minute slots

Appointment CRUD:

- `GET /api/appointments`
- `POST /api/appointments`
- `GET /api/appointments/{id}`
- `PATCH /api/appointments/{id}`
- `DELETE /api/appointments/{id}`

Citizens can update or delete only scheduled appointments.

The API prevents duplicate appointment slots.

---

## 11. Payments

iOS should use only:

- `card`
- `crypto`

Cash is not for the citizen app.

Endpoints:

- `GET /api/payments`
- `POST /api/payments`
- `GET /api/payments/{id}`
- `POST /api/payments/{id}/process`
- `GET /api/payments/{id}/receipt`

Payment filters:

- `status`
- `payment_method`
- `from_date`
- `to_date`

Payment creation:

```json
{
  "service_request_id": 12,
  "appointment_id": 5,
  "payment_method": "card",
  "amount": 5,
  "currency": "USD"
}
```

If `amount` is omitted, the backend uses the service base fee.

The current payment system is sandbox/mock:

- card returns a mock card confirmation next action
- crypto returns testnet wallet transfer instructions
- `/payments/{id}/process` simulates paid or failed status

Receipts are JSON only and available only for paid payments.

---

## 12. Certificates And Records

For completed service requests, citizens can retrieve a JSON certificate placeholder:

```http
GET /api/my-requests/{id}/certificate
```

The response includes:

- certificate number
- request reference
- service name
- citizen name
- office name
- issued date
- validity status

Completed official documents can also be downloaded through the request document download endpoint.

---

## 13. Feedback

Authenticated citizen endpoints:

- `GET /api/feedback`
- `POST /api/feedback`
- `GET /api/feedback/{id}`
- `PATCH /api/feedback/{id}`
- `DELETE /api/feedback/{id}`

Rules:

- feedback can be submitted only for completed requests
- one feedback per request
- citizens can update their own feedback

Public feedback endpoints:

- `GET /api/services/{id}/feedback`
- `GET /api/offices/{id}/feedback`

Public feedback exposes only safe fields:

- rating
- comment
- created date
- citizen first name or anonymized display name
- service name for office feedback

---

## 14. Notifications

The app supports in-app database notifications.

Endpoint:

```http
GET /api/notifications
```

Optional filter:

- `unread_only=true`

Notification response includes:

- id
- type
- title
- body
- icon
- deep link
- read date
- created date

Supported notification types:

- `request_update`
- `document_upload`
- `payment_update`
- `appointment_update`
- other/general

Mark read:

- `PATCH /api/notifications/{id}/read`
- `PATCH /api/notifications/read-all`

Raw Laravel notification data is not exposed to the iOS app.

---

## 15. Deprecated Or Not For iOS

The citizen iOS app should not call:

- `POST /api/register`
- `POST /api/verify-id`
- `POST /api/payments` with `payment_method = cash`
- `PATCH /api/payments/{id}/mark-paid`
- `PATCH /api/payments/{id}/refund`
- any `/staff/*`, `/admin/*`, or `/dashboard/*` route

---

## 16. Typical App Screens

## 16.1 Onboarding

- Welcome
- Identity document upload
- OCR preview and editable identity form
- Registration completion
- Google sign-in
- Apple sign-in
- Email login
- OTP verification

## 16.2 Home

- Featured categories
- Nearby offices
- Search services
- Recent requests
- Notifications shortcut

## 16.3 Services

- Category list
- Service list
- Service detail
- Required documents
- Service feedback
- Start request

## 16.4 Offices

- Office list
- Nearby offices
- Office detail
- Office location map
- Office services
- Office feedback

## 16.5 Requests

- Request history
- Request detail
- Status timeline
- Missing documents checklist
- Upload document
- Download official document
- QR tracking
- Cancel request

## 16.6 Appointments

- Availability picker
- Appointment booking
- Appointment details
- Reschedule scheduled appointment
- Cancel scheduled appointment

## 16.7 Payments

- Payment history
- Create card payment
- Create crypto payment
- Mock payment confirmation
- Receipt view

## 16.8 Feedback

- Submit rating and comment
- Edit feedback
- View public service/office feedback

## 16.9 Profile

- Current user profile
- Edit profile
- Change password
- Notification preferences
- Device token registration
- Logout

---

## 17. Implementation Notes For iOS

- Store Sanctum token securely in Keychain.
- Use bearer auth for all protected endpoints.
- Use multipart upload for identity and document upload endpoints.
- Use the normalized required document `key` as `document_type`.
- Generate QR codes client-side from `tracking_web_url` or `tracking_api_url`.
- Treat card and crypto payments as sandbox flows until real gateways are added.
- Poll notifications and request details for now; real push delivery is not wired.
- Use public endpoints for browsing before authentication.
- Use protected request/payment/profile endpoints only after token is available.
