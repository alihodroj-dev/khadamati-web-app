<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP | Khadamati</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
</head>

<body style="background: #f3f4f6; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: sans-serif;">

    <div style="width: 420px; background: white; padding: 36px; border-radius: 16px; border: 0.5px solid #e5e7eb; box-shadow: 0 4px 24px rgba(0,0,0,0.06);">
        <div style="width: 48px; height: 48px; background: #1e3a5f; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; margin-bottom: 20px;">
            <i class="ti ti-mail-code" style="font-size: 24px;" aria-hidden="true"></i>
        </div>

        <h1 style="font-size: 24px; font-weight: 600; color: #111827; margin: 0 0 6px;">Enter verification code</h1>
        <p style="font-size: 14px; color: #6b7280; margin: 0 0 24px;">
            We sent a 6-digit code to <strong>{{ $email }}</strong>. It expires after 5 minutes.
        </p>

        @if(session('status'))
            <div style="background: #eff6ff; border: 0.5px solid #bfdbfe; color: #1e40af; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 20px;">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div style="background: #fef2f2; border: 0.5px solid #fecaca; color: #991b1b; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 20px;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.otp.verify') }}">
            @csrf

            <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">
                Verification code
            </label>
            <input
                type="text"
                name="otp"
                inputmode="numeric"
                pattern="[0-9]{6}"
                maxlength="6"
                required
                autofocus
                placeholder="123456"
                style="width: 100%; padding: 12px 14px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 22px; letter-spacing: 8px; color: #111827; outline: none; box-sizing: border-box; text-align: center;"
            >

            <button type="submit"
                    style="width: 100%; padding: 11px; background: #1e3a5f; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; margin-top: 20px;">
                Verify and sign in
            </button>
        </form>

        <form method="POST" action="{{ route('login.otp.resend') }}" style="margin-top: 14px;">
            @csrf
            <button type="submit" style="width: 100%; background: none; border: none; color: #1e3a5f; cursor: pointer; font-size: 13px;">
                Send a new code
            </button>
        </form>

        <a href="{{ route('login') }}" style="display: block; text-align: center; margin-top: 18px; color: #6b7280; font-size: 13px; text-decoration: none;">
            Back to login
        </a>
    </div>

</body>
</html>
