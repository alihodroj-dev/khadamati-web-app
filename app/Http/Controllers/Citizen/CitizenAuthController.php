<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GoogleAuthenticationService;
use App\Services\GoogleIdTokenVerificationService;
use App\Services\AppleAuthenticationService;
use App\Services\AppleIdentityTokenVerificationService;
use App\Services\OtpLoginService;
use App\Services\IdentityPreviewService;
use App\Services\RegistrationCompletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Laravel\Socialite\Facades\Socialite;

class CitizenAuthController extends Controller
{
    public function __construct(
        private OtpLoginService $otpLoginService,
        private GoogleAuthenticationService $googleAuth,
        private AppleAuthenticationService $appleAuth,
        private IdentityPreviewService $identityPreviewService,
        private RegistrationCompletionService $registrationService
    ) {}

    // ========== LOGIN ==========
    public function showLoginForm()
    {
        return view('citizen.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $result = $this->otpLoginService->initiateLogin(
            $request->email,
            $request->password
        );

        if ($result['requires_otp']) {
            session(['challenge_token' => $result['challenge_token']]);
            return redirect()->route('citizen.auth.otp.form')
                ->with('success', 'Please enter the verification code sent to your email.');
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    public function showOtpForm()
    {
        if (!session('challenge_token')) {
            return redirect()->route('citizen.auth.login');
        }
        return view('citizen.auth.otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $challengeToken = session('challenge_token');
        
        if (!$challengeToken) {
            return redirect()->route('citizen.auth.login')
                ->withErrors(['otp' => 'Session expired. Please login again.']);
        }

        try {
            $user = $this->otpLoginService->verifyOtp($challengeToken, $request->otp);
            Auth::login($user, $request->boolean('remember'));
            session()->forget('challenge_token');
            
            return redirect()->route('citizen.dashboard');
        } catch (\Exception $e) {
            return back()->withErrors(['otp' => $e->getMessage()]);
        }
    }

    public function resendOtp(Request $request)
    {
        $challengeToken = session('challenge_token');
        
        if (!$challengeToken) {
            return redirect()->route('citizen.auth.login')
                ->withErrors(['email' => 'Session expired. Please login again.']);
        }

        try {
            $result = $this->otpLoginService->resendOtp($challengeToken);
            return back()->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->withErrors(['otp' => $e->getMessage()]);
        }
    }

    // ========== REGISTRATION ==========
    public function showRegisterForm()
    {
        return view('citizen.auth.register');
    }

    public function showIdentityVerificationForm()
    {
        return view('citizen.auth.verify-id');
    }

    public function processIdentityVerification(Request $request)
    {
        $request->validate([
            'id_front' => ['required', 'image', 'max:5120'],
            'id_back' => ['required', 'image', 'max:5120'],
        ]);

        try {
            $result = $this->identityPreviewService->createPreview(
                $request->file('id_front'),
                $request->file('id_back')
            );

            session(['verification_session_token' => $result['verification_session_token']]);

            return view('citizen.auth.complete-registration', [
                'fields' => $result['fields'],
                'verification_session_token' => $result['verification_session_token'],
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function completeRegistration(Request $request)
    {
        $request->validate([
            'verification_session_token' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'father_name' => ['required', 'string', 'max:255'],
            'mother_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date'],
            'national_id' => ['required', 'string', 'unique:users,national_id'],
            'phone' => ['nullable', 'string'],
            'auth_provider' => ['required', 'in:email,google,apple'],
            'password' => ['required_if:auth_provider,email', 'nullable', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $user = $this->registrationService->complete($request->all());
            Auth::login($user);
            
            return redirect()->route('citizen.dashboard')
                ->with('success', 'Registration completed successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // ========== SOCIAL LOGIN ==========
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $socialUser = Socialite::driver('google')->user();
            $user = $this->googleAuth->authenticate([
                'sub' => $socialUser->id,
                'email' => $socialUser->email,
                'name' => $socialUser->name,
                'picture' => $socialUser->avatar,
            ]);
            Auth::login($user);
            return redirect()->route('citizen.dashboard');
        } catch (\Exception $e) {
            return redirect()->route('citizen.auth.login')
                ->withErrors(['email' => 'Google login failed: ' . $e->getMessage()]);
        }
    }

    // ========== LOGOUT ==========
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('citizen.auth.login');
    }
}