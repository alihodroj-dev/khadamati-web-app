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

            {{-- Success Message --}}
            @if(session('success'))
                <div style="background: #f0fdf4; border: 0.5px solid #bbf7d0; color: #166534; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <i class="ti ti-circle-check" style="font-size: 16px;" aria-hidden="true"></i>
                    <span>{{ session('success') }}</span>
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
                        Password
                    </label>
                    <div style="position: relative;">
                        <i class="ti ti-lock" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 16px;" aria-hidden="true"></i>
                        <input type="password"
                               name="password"
                               required
                               placeholder="••••••••"
                               style="width: 100%; padding: 10px 14px 10px 38px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; color: #111827; outline: none; box-sizing: border-box;"
                               onfocus="this.style.borderColor='#1e3a5f'; this.style.boxShadow='0 0 0 3px rgba(30,58,95,0.08)'"
                               onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
                    </div>
                </div>

                {{-- Remember me --}}
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #6b7280; cursor: pointer;">
                        <input type="checkbox" name="remember" style="accent-color: #1e3a5f;">
                        Remember me
                    </label>
                    <a href="{{ route('citizen.auth.register') }}" style="font-size: 13px; color: #1e3a5f; text-decoration: none;">
                        Register as Citizen
                    </a>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        style="width: 100%; padding: 11px; background: #1e3a5f; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer;"
                        onmouseover="this.style.background='#162d4a'"
                        onmouseout="this.style.background='#1e3a5f'">
                    Sign in
                </button>

            </form>

            {{-- OR Divider --}}
            <div style="position: relative; margin: 24px 0 16px;">
                <div style="position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: #e5e7eb;"></div>
                <div style="position: relative; text-align: center;">
                    <span style="background: white; padding: 0 12px; font-size: 12px; color: #9ca3af;">Or continue with</span>
                </div>
            </div>

            {{-- Google Sign In Button --}}
            <a href="{{ route('citizen.auth.google') }}" 
            style="display: flex; align-items: center; justify-content: center; gap: 12px; width: 100%; padding: 10px; background: white; border: 1px solid #e5e7eb; border-radius: 8px; cursor: pointer; text-decoration: none; transition: background 0.2s;"
            onmouseover="this.style.background='#f8f9fa'"
            onmouseout="this.style.background='white'">
                <svg width="20" height="20" viewBox="0 0 24 24">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                <span style="color: #3c4043; font-size: 14px; font-weight: 500;">Sign in with Google</span>
            </a>

        </div>

    </div>

    <script>
        function fillCredentials(email, password) {
            document.querySelector('input[name="email"]').value = email;
            document.querySelector('input[name="password"]').value = password;
        }
    </script>

</body>
</html>