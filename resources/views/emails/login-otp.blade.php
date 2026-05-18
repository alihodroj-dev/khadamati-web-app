<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Khadamati login code</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    <p>Hello {{ $user->name }},</p>

    <p>Use this verification code to sign in to Khadamati:</p>

    <p style="font-size: 28px; font-weight: bold; letter-spacing: 6px; color: #1e3a5f;">
        {{ $otp }}
    </p>

    <p>This code expires in {{ $expiresInMinutes }} minutes.</p>

    <p>If you did not request this code, you can safely ignore this email.</p>
</body>
</html>
