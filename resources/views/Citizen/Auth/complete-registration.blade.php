<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Registration | Khadamati</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
</head>

<body style="background: #f3f4f6; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: sans-serif;">

    <div style="width: 750px; background: white; border-radius: 16px; border: 0.5px solid #e5e7eb; box-shadow: 0 4px 24px rgba(0,0,0,0.06); overflow: hidden;">

        <div style="background: #1e3a5f; padding: 20px 32px; display: flex; align-items: center; gap: 12px;">
            <i class="ti ti-article" style="color: white; font-size: 28px;"></i>
            <div>
                <p style="color: white; font-size: 18px; font-weight: 500; margin: 0;">Complete Your Profile</p>
                <p style="color: rgba(255,255,255,0.7); font-size: 12px; margin: 0;">Review and confirm your information</p>
            </div>
        </div>

        <div style="padding: 32px;">

            @if(session('error'))
                <div style="background: #fef2f2; border: 0.5px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 8px; font-size: 13px; margin-bottom: 24px;">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div style="background: #fef2f2; border: 0.5px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 8px; font-size: 13px; margin-bottom: 24px;">
                    @foreach($errors->all() as $error)
                        <p style="margin: 4px 0;">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('citizen.auth.register.complete') }}">
                @csrf
                <input type="hidden" name="verification_session_token" value="{{ $verification_session_token }}">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    @foreach($fields as $field)
                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">
                                {{ $field['label'] }}
                                @if($field['required'])
                                    <span style="color: #ef4444;">*</span>
                                @endif
                            </label>
                            <input type="{{ $field['type'] }}"
                                   name="{{ $field['key'] }}"
                                   value="{{ $field['value'] }}"
                                   @if($field['required']) required @endif
                                   style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
                        </div>
                    @endforeach
                </div>

                <!-- Email Field (Required for registration) -->
                <div style="margin-top: 16px; margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">
                        Email Address <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email', $registrationData['email'] ?? '') }}" required
                           style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
                </div>

                <!-- Phone Field -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">
                        Phone Number
                    </label>
                    <input type="tel" name="phone" value="{{ old('phone', $registrationData['phone'] ?? '') }}"
                           style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
                </div>

                <!-- Password Fields -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">
                        Password <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="password" name="password" required
                           style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
                    <p style="font-size: 11px; color: #9ca3af; margin-top: 4px;">Minimum 8 characters</p>
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">
                        Confirm Password <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="password" name="password_confirmation" required
                           style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
                </div>

                <div style="background: #f0fdf4; border: 0.5px solid #bbf7d0; padding: 12px; border-radius: 8px; margin-bottom: 24px;">
                    <p style="font-size: 12px; color: #166534; margin: 0; display: flex; align-items: center; gap: 6px;">
                        <i class="ti ti-check" style="font-size: 14px;"></i>
                        Your identity has been verified. Click below to complete registration.
                    </p>
                </div>

                <button type="submit"
                        style="width: 100%; padding: 12px; background: #1e3a5f; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer;"
                        onmouseover="this.style.background='#162d4a'"
                        onmouseout="this.style.background='#1e3a5f'">
                    Complete Registration
                </button>
            </form>

        </div>

    </div>

</body>
</html>