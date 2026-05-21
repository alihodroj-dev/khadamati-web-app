<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Khadamati</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
</head>

<body style="background: #f3f4f6; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: sans-serif;">

    <div style="display: flex; width: 900px; min-height: 560px; border-radius: 16px; overflow: hidden; border: 0.5px solid #e5e7eb; box-shadow: 0 4px 24px rgba(0,0,0,0.06);">

        {{-- LEFT PANEL --}}
        <div style="width: 42%; background: #1e3a5f; display: flex; flex-direction: column; justify-content: space-between; padding: 40px 36px;">

            {{-- Brand --}}
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 42px; height: 42px; background: #3b82f6; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; font-weight: 500;">
                    K
                </div>
                <div>
                    <p style="color: white; font-size: 17px; font-weight: 500; margin: 0;">Khadamati</p>
                    <p style="color: rgba(255,255,255,0.45); font-size: 12px; margin: 0;">Government Portal</p>
                </div>
            </div>

            {{-- Middle content --}}
            <div>
                <p style="color: white; font-size: 22px; font-weight: 500; margin: 0 0 12px; line-height: 1.3;">
                    Manage government services efficiently
                </p>
                <p style="color: rgba(255,255,255,0.55); font-size: 14px; line-height: 1.7; margin: 0 0 24px;">
                    A centralized platform for administrators and staff to handle citizen requests, appointments, and payments.
                </p>

                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                    <i class="ti ti-shield-check" style="color: #3b82f6; font-size: 18px;" aria-hidden="true"></i>
                    <span style="color: rgba(255,255,255,0.7); font-size: 13px;">Role-based secure access</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                    <i class="ti ti-clipboard-list" style="color: #3b82f6; font-size: 18px;" aria-hidden="true"></i>
                    <span style="color: rgba(255,255,255,0.7); font-size: 13px;">Request tracking & management</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="ti ti-chart-bar" style="color: #3b82f6; font-size: 18px;" aria-hidden="true"></i>
                    <span style="color: rgba(255,255,255,0.7); font-size: 13px;">Analytics & reporting</span>
                </div>
            </div>

            {{-- Footer --}}
            <p style="color: rgba(255,255,255,0.3); font-size: 12px; margin: 0;">
                © {{ date('Y') }} Khadamati. All rights reserved.
            </p>

        </div>

        {{-- RIGHT PANEL --}}
        <div style="flex: 1; background: white; padding: 48px 44px; display: flex; flex-direction: column; justify-content: center;">

            <p style="font-size: 24px; font-weight: 600; color: #111827; margin: 0 0 6px;">Welcome back</p>
            <p style="font-size: 14px; color: #9ca3af; margin: 0 0 32px;">Sign in to your dashboard</p>

            {{-- Status --}}
            @if(session('status'))
                <div style="background: #eff6ff; border: 0.5px solid #bfdbfe; color: #1e40af; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <i class="ti ti-mail-check" style="font-size: 16px;" aria-hidden="true"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Errors --}}
            @if($errors->any())
                <div style="background: #fef2f2; border: 0.5px solid #fecaca; color: #991b1b; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <i class="ti ti-circle-x" style="font-size: 16px;" aria-hidden="true"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ url('/login') }}">
                @csrf

                {{-- Email --}}
                <div style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">
                        Email address
                    </label>
                    <div style="position: relative;">
                        <i class="ti ti-mail" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 16px;" aria-hidden="true"></i>
                        <input type="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               placeholder="you@example.com"
                               style="width: 100%; padding: 10px 14px 10px 38px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; color: #111827; outline: none; box-sizing: border-box;"
                               onfocus="this.style.borderColor='#1e3a5f'; this.style.boxShadow='0 0 0 3px rgba(30,58,95,0.08)'"
                               onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
                    </div>
                </div>

                {{-- Password --}}
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px;">
                        Password <span style="color: #9ca3af; font-weight: 400;">(admin only)</span>
                    </label>
                    <div style="position: relative;">
                        <i class="ti ti-lock" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 16px;" aria-hidden="true"></i>
                        <input type="password"
                               name="password"
                               placeholder="Required for admin accounts"
                               style="width: 100%; padding: 10px 14px 10px 38px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; color: #111827; outline: none; box-sizing: border-box;"
                               onfocus="this.style.borderColor='#1e3a5f'; this.style.boxShadow='0 0 0 3px rgba(30,58,95,0.08)'"
                               onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
                    </div>
                    <p style="font-size: 12px; color: #9ca3af; margin: 6px 0 0;">
                        Staff and citizen accounts will receive a one-time email code.
                    </p>
                </div>

                {{-- Remember me --}}
                <div style="display: flex; align-items: center; margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #6b7280; cursor: pointer;">
                        <input type="checkbox" name="remember" style="accent-color: #1e3a5f;">
                        Remember me
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        style="width: 100%; padding: 11px; background: #1e3a5f; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer;"
                        onmouseover="this.style.background='#162d4a'"
                        onmouseout="this.style.background='#1e3a5f'">
                    Continue
                </button>

            </form>

        </div>

    </div>

</body>
</html>
