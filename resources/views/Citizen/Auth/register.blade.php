<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Khadamati</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
</head>

<body style="background: #f3f4f6; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: sans-serif;">

    <div style="display: flex; width: 1000px; min-height: 600px; border-radius: 16px; overflow: hidden; border: 0.5px solid #e5e7eb; box-shadow: 0 4px 24px rgba(0,0,0,0.06); background: white;">

        <div style="flex: 1; padding: 40px; overflow-y: auto; max-height: 80vh;">

            <div style="text-align: center; margin-bottom: 24px;">
                <div style="width: 56px; height: 56px; background: #1e3a5f; border-radius: 28px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                    <i class="ti ti-user-plus" style="color: white; font-size: 28px;"></i>
                </div>
                <p style="font-size: 24px; font-weight: 600; color: #111827; margin: 0 0 6px;">Create Account</p>
                <p style="font-size: 14px; color: #6b7280; margin: 0;">Register as a citizen to access government services</p>
            </div>

            @if($errors->any())
                <div style="background: #fef2f2; border: 0.5px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 8px; font-size: 13px; margin-bottom: 20px;">
                    @foreach($errors->all() as $error)
                        <p style="margin: 4px 0;">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('citizen.auth.register.store') }}">
                @csrf

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">First Name <span style="color: #ef4444;">*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required
                               style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Last Name <span style="color: #ef4444;">*</span></label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required
                               style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Father Name <span style="color: #ef4444;">*</span></label>
                        <input type="text" name="father_name" value="{{ old('father_name') }}" required
                               style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Mother Name <span style="color: #ef4444;">*</span></label>
                        <input type="text" name="mother_name" value="{{ old('mother_name') }}" required
                               style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Date of Birth <span style="color: #ef4444;">*</span></label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required
                               style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">National ID <span style="color: #ef4444;">*</span></label>
                        <input type="text" name="national_id" value="{{ old('national_id') }}" required
                               style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Email <span style="color: #ef4444;">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Phone (Optional)</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}"
                               style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Password <span style="color: #ef4444;">*</span></label>
                        <input type="password" name="password" required
                               style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">Confirm Password <span style="color: #ef4444;">*</span></label>
                        <input type="password" name="password_confirmation" required
                               style="width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box;">
                    </div>
                </div>

                <div style="background: #f9fafb; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                    <p style="font-size: 12px; color: #374151; margin: 0;">
                        <i class="ti ti-shield-check" style="color: #1e3a5f;"></i>
                        <strong>Next Step:</strong> You'll upload your National ID for verification.
                    </p>
                </div>

                <button type="submit"
                        style="width: 100%; padding: 12px; background: #1e3a5f; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer;"
                        onmouseover="this.style.background='#162d4a'"
                        onmouseout="this.style.background='#1e3a5f'">
                    Continue to ID Verification →
                </button>
            </form>

            <div style="text-align: center; margin-top: 20px;">
                <a href="{{ route('login') }}" style="font-size: 13px; color: #6b7280; text-decoration: none;">
                    ← Already have an account? Sign in
                </a>
            </div>

        </div>

        <div style="width: 35%; background: #1e3a5f; padding: 40px 30px; color: white;">
            <p style="font-size: 18px; font-weight: 500; margin: 0 0 16px;">Why register?</p>
            <div style="margin-bottom: 16px;">
                <i class="ti ti-file-description" style="font-size: 18px; margin-bottom: 6px; display: block;"></i>
                <p style="font-size: 12px; opacity: 0.8; margin: 0;">Submit service requests online</p>
            </div>
            <div style="margin-bottom: 16px;">
                <i class="ti ti-credit-card" style="font-size: 18px; margin-bottom: 6px; display: block;"></i>
                <p style="font-size: 12px; opacity: 0.8; margin: 0;">Pay securely online or with crypto</p>
            </div>
            <div style="margin-bottom: 16px;">
                <i class="ti ti-calendar" style="font-size: 18px; margin-bottom: 6px; display: block;"></i>
                <p style="font-size: 12px; opacity: 0.8; margin: 0;">Book appointments easily</p>
            </div>
            <div>
                <i class="ti ti-message-circle" style="font-size: 18px; margin-bottom: 6px; display: block;"></i>
                <p style="font-size: 12px; opacity: 0.8; margin: 0;">Get real-time updates</p>
            </div>
        </div>

    </div>

</body>
</html>