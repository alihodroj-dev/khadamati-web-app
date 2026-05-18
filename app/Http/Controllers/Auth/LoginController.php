<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpLoginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    public function __construct(
        private OtpLoginService $otpLoginService
    ) {}

    public function showLogin()
    {
        return view('Auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Invalid credentials.',
            ]);
        }

        // Check if user is active
        if (!$user->is_active) {
            return back()->withErrors([
                'email' => 'Your account is inactive. Please contact support.',
            ]);
        }

        // Handle CITIZEN login with 2FA
        if ($user->role === 'citizen') {
            try {
                $result = $this->otpLoginService->initiateLogin(
                    $credentials['email'],
                    $credentials['password']
                );
                
                session(['citizen_challenge_token' => $result['challenge_token']]);
                session(['citizen_login_email' => $credentials['email']]);
                session(['citizen_intended_url' => route('citizen.dashboard')]);
                
                return redirect()->route('citizen.auth.otp.form')
                    ->with('success', 'Please enter the verification code sent to your email.');
                    
            } catch (\Exception $e) {
                return back()->withErrors([
                    'email' => $e->getMessage(),
                ]);
            }
        }

        // Handle ADMIN login (no 2FA for now)
        if ($user->role === 'admin') {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        // Handle STAFF login (no 2FA for now)
        if ($user->role === 'staff') {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            return redirect()->route('staff.dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ]);   
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}