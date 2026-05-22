# Firebase Cloud Messaging

## Install

```bash
composer require kreait/laravel-firebase -W
php artisan vendor:publish --provider="Kreait\Laravel\Firebase\ServiceProvider" --tag=config
php artisan migrate
```

If local Composer reports that `ext-sodium` is missing, enable Sodium in your PHP installation. This workspace installed the package with:

```bash
composer require kreait/laravel-firebase -W --ignore-platform-req=ext-sodium
```

## Apple (APNs) in Firebase Console

iOS delivery only works after APNs is linked to the Firebase project **`khadamati-2005`**:

1. Open [Firebase Console](https://console.firebase.google.com/) → **Project settings** → **Cloud Messaging**.
2. Under **Apple app configuration**, upload either:
   - **APNs Authentication Key** (.p8) — recommended, or
   - **APNs Certificate** (.p12) — e.g. `Khadamati Push Cert.p12` on your machine.
3. Bundle ID must match the iOS app: `com.alihodroj.Khadamati`.
4. Debug/Xcode builds use the **development (sandbox)** APNs environment; release/TestFlight use **production**. Mismatch between cert/key environment and build type is a common reason test pushes never appear.

## Environment

The Firebase service account JSON is expected here:

```text
storage/app/firebase/firebase-service-account.json
```

Add this to `.env`:

```env
FIREBASE_PROJECT=app
FIREBASE_CREDENTIALS=storage/app/firebase/firebase-service-account.json
```

Then clear cached config:

```bash
php artisan config:clear
```

Without the service account file and env vars, Laravel cannot send pushes (`composer install` must also be run so `kreait/laravel-firebase` is present in `vendor/`).

## Save FCM Token

Authenticated users can save or replace their current FCM token:

```http
POST /api/fcm-token
Authorization: Bearer YOUR_SANCTUM_TOKEN
Content-Type: application/json

{
  "fcm_token": "DEVICE_FCM_TOKEN_FROM_IOS_OR_ANDROID"
}
```

Remove it on logout or when the app tells you the token is invalid:

```http
DELETE /api/fcm-token
Authorization: Bearer YOUR_SANCTUM_TOKEN
```

## Sending Example

Inject `App\Services\FirebaseNotificationService` anywhere you need push notifications:

```php
use App\Models\ServiceRequest;
use App\Services\FirebaseNotificationService;

public function notifyStatusChanged(
    ServiceRequest $serviceRequest,
    FirebaseNotificationService $firebase
): void {
    $firebase->sendToUser(
        $serviceRequest->user,
        'Request status updated',
        'Your request is now '.$serviceRequest->status.'.',
        [
            'type' => 'request_status_update',
            'service_request_id' => $serviceRequest->id,
            'status' => $serviceRequest->status,
        ]
    );
}
```

Suggested future `type` values:

- `request_status_update`
- `account_approval`
- `feedback_reply`
- `new_message`
- `appointment_reminder`
