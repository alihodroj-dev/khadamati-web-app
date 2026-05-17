# Admin & office portal — deferred features

This document records features **intentionally not implemented yet** for the admin/staff web portal and related office workflows. It is the source of truth for future work; code comments tagged `DEFERRED(roadmap)` point here.

**Do not** add partial implementations (stub tables, Pusher, Fortify 2FA packages, chat UI shells, or reminder cron jobs) until a feature is explicitly scheduled—keep the current database-notification and session-login behavior stable.

---

## Two-factor authentication (2FA)

### Expected future behavior

- After email/password succeeds on **web** `/login`, staff and admin users with 2FA enabled are challenged for a second factor (TOTP app and/or recovery codes) before `session` is fully established.
- Admins can require 2FA per office or globally; users can enroll from a profile/security page.
- Failed or missing 2FA does not create a privileged session; API Sanctum tokens for citizens are unchanged unless a separate mobile 2FA policy is added later.
- Trusted devices (optional) remember the browser for N days.

### Likely tables / services

| Artifact | Purpose |
|----------|---------|
| `users.two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at` | Standard Laravel/Fortify-style columns |
| `user_trusted_devices` (optional) | Device fingerprint, expiry |
| `App\Services\TwoFactorEnrollmentService` | Secret generation, QR, confirm |
| `App\Http\Middleware\EnsureTwoFactorVerified` | Block admin/staff routes until verified |
| `LoginController` (or dedicated `TwoFactorChallengeController`) | Challenge step after `Auth::attempt` |

### Existing extension points

- `App\Http\Controllers\Auth\LoginController` — web session login for `admin` and `staff` roles.
- `App\Http\Middleware\AdminMiddleware`, `StaffMiddleware` — run **after** 2FA middleware once introduced.
- `routes/web.php` — guest `login` group vs `auth` group; challenge routes fit between them.
- Citizen API auth (`App\Http\Controllers\Api\AuthController`, `OtpLoginService`) — separate flow; do not conflate with office 2FA without a product decision.

### What not to touch yet

- Do not change citizen OTP/login API contracts.
- Do not add 2FA columns or migrations “just in case.”
- Do not install Fortify/Laravel Breeze until the enrollment UX is designed.
- Do not weaken `AdminMiddleware` / `StaffMiddleware` role checks.

---

## Live real-time notifications

### Expected future behavior

- When a new service request is assigned, a citizen uploads a document, or status changes, **office users** see the navbar badge and dropdown update without a full page reload (and optionally hear/desktop-notify).
- Mobile citizens may continue using push via `device_tokens`; web staff use WebSockets or SSE subscribed to a private channel per user (e.g. `App.Models.User.{id}`).
- **Database notifications remain the source of truth**; broadcast is an delivery layer on top of rows already written to `notifications`.

### Likely tables / services

| Artifact | Purpose |
|----------|---------|
| `config/broadcasting.php`, `BROADCAST_CONNECTION` | Pusher/Ably/Reverb config |
| `routes/channels.php` | Authorize private user channels |
| `ShouldBroadcast` / `broadcast` channel on notification events | Push payload to front end |
| Front-end: Laravel Echo + Alpine listener on navbar | Increment unread count, prepend item |
| Optional: `notification_deliveries` | Audit push vs database (later) |

### Existing extension points

- `App\Notifications\*` (`RequestUpdatedNotification`, `DocumentUploadedNotification`, `AppointmentUpdatedNotification`, `PaymentUpdatedNotification`) — today `via()` returns `['database']` only; add `broadcast` or event listeners here.
- `App\Http\Controllers\Api\RequestDocumentController::notifyAssignedStaffOfCitizenUpload` — staff alert on citizen upload.
- `App\Http\Controllers\Api\StaffRequestController`, `Staff\StaffRequestController` — assignment/status notifies staff/citizen.
- `App\Http\View\Composers\NavbarComposer` — loads latest 8 + unread count on each request.
- `App\Http\Controllers\NotificationController` + `App\Http\Controllers\Api\NotificationController` — read/mark-all APIs unchanged.
- `App\Support\NotificationPresentation`, `WebNotificationHelper` — shared payload shape for API and Blade.
- `resources/views/components/navbar.blade.php` — dropdown UI target for live updates.

### What not to touch yet

- Do not add Pusher/Echo/Reverb dependencies or `VITE_*` broadcast keys in this repo until scheduled.
- Do not replace database notifications with broadcast-only delivery.
- Do not add polling loops in production without an explicit decision (prefer broadcast when implemented).
- Keep `NavbarComposer` server-rendered; live updates should be additive JS.

---

## In-app chat with citizens

### Expected future behavior

- Threaded conversation **per service request** between assigned staff (and optionally other office staff) and the citizen who owns the request.
- Messages support text and (later) attachments; read receipts optional.
- Staff see chat on request detail; citizens see chat in the mobile app on the request screen.
- Office scoping: staff only access threads for requests in their office (`StaffOfficeScope`).

### Likely tables / services

| Artifact | Purpose |
|----------|---------|
| `request_conversations` | One row per `service_request_id` |
| `request_messages` | `conversation_id`, `user_id`, `body`, `read_at`, timestamps |
| `App\Models\RequestConversation`, `RequestMessage` | Eloquent + policies |
| `App\Http\Controllers\Api\RequestMessageController` | Citizen API |
| `App\Http\Controllers\Staff\RequestMessageController` (or Livewire) | Web staff UI |
| `App\Policies\RequestConversationPolicy` | Citizen owner + office staff |

### Existing extension points

- `App\Models\ServiceRequest` — `user_id`, `assigned_staff_id`, `office_id`, `citizen_notes` / `staff_notes` (static fields today, not a thread).
- `App\Support\StaffOfficeScope` — reuse for authorization.
- `resources/views/Staff/Requests/show.blade.php` — natural place for a chat panel.
- `App\Http\Controllers\Staff\StaffRequestController`, `Api\StaffRequestController` — request detail entry points.
- Feedback (`feedback`, `feedback_responses`) — **public/office feedback**, not private chat; do not overload those tables.

### What not to touch yet

- Do not repurpose `citizen_notes` / `staff_notes` as chat messages (they are submission metadata).
- Do not add chat routes or migrations until the API contract with iOS is agreed.
- Do not merge chat into `notifications` rows.

---

## Email / SMS appointment reminders

### Expected future behavior

- Scheduled reminders before `appointments.appointment_date` + `appointment_time` (e.g. 24h and 1h), respecting user preferences.
- Channels: email (Mailable), SMS (Twilio/etc.), and optional push for citizens who already use `device_tokens`.
- Staff may receive reminders for appointments they host; citizens receive booking confirmations and changes (today partially covered by in-app **database** `AppointmentUpdatedNotification` only).
- Cancellations and reschedules cancel or reschedule queued reminder jobs.

### Likely tables / services

| Artifact | Purpose |
|----------|---------|
| `appointment_reminders` | `appointment_id`, `channel`, `scheduled_for`, `sent_at`, `status` |
| `App\Console\Commands\SendAppointmentReminders` | Dispatched from scheduler |
| `App\Mail\AppointmentReminderMail`, `App\Notifications\AppointmentReminderNotification` | Channel-specific sending |
| `App\Services\AppointmentReminderScheduler` | Enqueue on create/update/cancel |
| `config/services.php` | SMS provider credentials |

### Existing extension points

- `App\Models\Appointment` — date, time, status, `user_id`, `staff_id`, `service_request_id`.
- `App\Http\Controllers\Api\AppointmentController` — create/update notifies via `AppointmentUpdatedNotification` (database).
- `App\Models\User` — `email`, `phone`, `email_notifications_enabled`, `sms_notifications_enabled` (preferences today limited to **citizens** in `ProfileController::updateNotificationPreferences`).
- `routes/console.php` — no scheduler entries yet; future `Schedule::command(...)->everyMinute()`.
- Staff web: `Staff\AppointmentController`, `Admin\AppointmentController`.

### What not to touch yet

- Do not send real SMS/email from appointment create/update until templates and compliance (opt-in) are defined.
- Do not enable `sms_notifications_enabled` for staff in API without product rules.
- Do not add fake reminder rows or cron in CI without feature flag.
- Keep existing `AppointmentUpdatedNotification` database payloads stable for mobile.

---

## How to pick up a deferred feature

1. Read the section above and search the codebase for `DEFERRED(roadmap)`.
2. Add migrations and tests in a **dedicated PR** per feature.
3. Update this document: move the feature to “Implemented” with date and PR link, or adjust scope if plans changed.
