<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpLoginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    // DEFERRED(roadmap): 2FA challenge after password for staff/admin web login.
    // See docs/admin-office-roadmap.md#two-factor-authentication-2fa

    public function showLogin()
    {
        return view('Auth.login');
    }

    public function login(Request $request, OtpLoginService $otpLoginService)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['nullable', 'string'],
            'remember' => ['nullable'],
        ]);

        $user = User::query()
            ->where('email', strtolower(trim($validated['email'])))
            ->first();

        if (! $user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid credentials.']);
        }

        if (! $user->is_active) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Your account is inactive.']);
        }

        if ($user->isAdmin()) {
            if (empty($validated['password'])) {
                return back()
                    ->withInput($request->only('email'))
                    ->withErrors(['password' => 'Password is required for admin accounts.']);
            }

            if (Auth::attempt([
                'email' => $validated['email'],
                'password' => $validated['password'],
                'is_active' => true,
            ], $request->boolean('remember'))) {
                $request->session()->regenerate();

                return redirect()->route('admin.dashboard');
            }

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid credentials.']);
        }

        try {
            $challenge = $otpLoginService->requestOtpForExistingUser(
                $validated['email'],
                [User::ROLE_CITIZEN, User::ROLE_STAFF]
            );
        } catch (ValidationException $exception) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors($exception->errors());
        }

        $request->session()->put('otp_login.challenge_token', $challenge['challenge_token']);
        $request->session()->put('otp_login.email', strtolower(trim($validated['email'])));
        $request->session()->put('otp_login.remember', $request->boolean('remember'));

        return redirect()
            ->route('login.otp.show')
            ->with('status', 'We sent a 6-digit verification code to your email.');
    }

    public function showOtpForm(Request $request)
    {
        if (! $request->session()->has('otp_login.challenge_token')) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Please request a verification code first.']);
        }

        return view('Auth.verify-otp', [
            'email' => $request->session()->get('otp_login.email'),
        ]);
    }

    public function verifyOtp(Request $request, OtpLoginService $otpLoginService)
    {
        $validated = $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ]);

        $challengeToken = $request->session()->get('otp_login.challenge_token');

        if (! $challengeToken) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Please request a verification code first.']);
        }

        try {
            $user = $otpLoginService->verifyOtp($challengeToken, $validated['otp']);
        } catch (ValidationException $exception) {
            return back()
                ->withErrors($exception->errors());
        }

        if ($user->isAdmin()) {
            Auth::logout();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Admin accounts must use password sign-in.']);
        }

        Auth::login($user, (bool) $request->session()->get('otp_login.remember', false));

        $request->session()->forget('otp_login');
        $request->session()->regenerate();

        return redirect()->intended($this->redirectPathFor($user));
    }

    public function resendOtp(Request $request, OtpLoginService $otpLoginService)
    {
        $challengeToken = $request->session()->get('otp_login.challenge_token');

        if (! $challengeToken) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Please request a verification code first.']);
        }

        try {
            $result = $otpLoginService->resendOtp($challengeToken);
        } catch (ValidationException $exception) {
            return redirect()
                ->route('login')
                ->withErrors($exception->errors());
        }

        $request->session()->put('otp_login.challenge_token', $result['challenge_token']);

        return back()->with('status', 'A new verification code has been sent.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function redirectPathFor(User $user): string
    {
        if ($user->isStaff()) {
            return route('staff.dashboard');
        }

        return route('notifications.index');
    }
}
