<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpLoginService;
use App\Services\IdentityPreviewService;
use App\Services\RegistrationCompletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class CitizenAuthController extends Controller
{
    public function __construct(
        private OtpLoginService $otpLoginService,
        private IdentityPreviewService $identityPreviewService,
        private RegistrationCompletionService $registrationService
    ) {}

    // ========== LOGIN & OTP METHODS ==========
    
    public function showOtpForm()
    {
        if (!session('citizen_challenge_token')) {
            return redirect()->route('login')
                ->with('error', 'Please login first.');
        }
        
        $debugOtp = session('debug_otp');
        
        return view('citizen.auth.otp', compact('debugOtp'));
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $challengeToken = session('citizen_challenge_token');
        $intendedUrl = session('citizen_intended_url', route('citizen.dashboard'));
        
        if (!$challengeToken) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Session expired. Please login again.']);
        }

        try {
            $user = $this->otpLoginService->verifyOtp($challengeToken, $request->otp);
            
            Auth::login($user, $request->boolean('remember'));
            
            session()->forget(['citizen_challenge_token', 'citizen_intended_url', 'debug_otp']);
            
            Log::info('Citizen logged in successfully', ['user_id' => $user->id]);
            
            return redirect()->to($intendedUrl)
                ->with('success', 'Welcome back, ' . $user->name . '!');
                
        } catch (\Exception $e) {
            Log::error('OTP verification failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['otp' => $e->getMessage()]);
        }
    }

    public function resendOtp(Request $request)
    {
        $challengeToken = session('citizen_challenge_token');
        
        if (!$challengeToken) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Session expired. Please login again.']);
        }

        try {
            $result = $this->otpLoginService->resendOtp($challengeToken);
            return back()->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->withErrors(['otp' => $e->getMessage()]);
        }
    }

    // ========== REGISTRATION METHODS ==========
    
    public function showRegisterForm()
    {
        return view('citizen.auth.register');
    }

    // ADD THIS METHOD:
    public function storeRegistrationStep1(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'father_name' => ['required', 'string', 'max:255'],
            'mother_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date'],
            'national_id' => ['required', 'string', 'unique:users,national_id'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Store registration data in session
        session(['registration_data' => $validated]);

        // Redirect to ID upload page
        return redirect()->route('citizen.auth.verify-id')
            ->with('success', 'Please upload your ID for verification.');
    }

    public function showIdentityVerificationForm()
    {
        // Check if registration data exists
        if (!session('registration_data')) {
            return redirect()->route('citizen.auth.register')
                ->withErrors(['error' => 'Please fill out the registration form first.']);
        }
        
        return view('citizen.auth.verify-id');
    }

public function processIdentityVerification(Request $request)
{
    // Validate the ID files
    $request->validate([
        'id_front' => ['required', 'image', 'max:5120'],
        'id_back' => ['required', 'image', 'max:5120'],
    ]);

    try {
        // Store the uploaded ID files temporarily
        $frontPath = $request->file('id_front')->store('temp/id-front', 'public');
        $backPath = $request->file('id_back')->store('temp/id-back', 'public');

        // Get registration data from session (from step 1)
        $registrationData = session('registration_data');
        
        if (!$registrationData) {
            return redirect()->route('citizen.auth.register')
                ->withErrors(['error' => 'Registration data missing. Please start over.']);
        }

        // Create a mock verification session
        $sessionToken = \Illuminate\Support\Str::random(64);
        
        // Store in session for the next step
        session(['verification_session_token' => $sessionToken]);
        session(['temp_id_front' => $frontPath]);
        session(['temp_id_back' => $backPath]);

        // Build the fields array from registration data
        $fields = [
            [
                'key' => 'first_name',
                'label' => 'First Name',
                'type' => 'text',
                'value' => $registrationData['first_name'] ?? '',
                'editable' => true,
                'required' => true,
            ],
            [
                'key' => 'last_name',
                'label' => 'Last Name',
                'type' => 'text',
                'value' => $registrationData['last_name'] ?? '',
                'editable' => true,
                'required' => true,
            ],
            [
                'key' => 'father_name',
                'label' => 'Father Name',
                'type' => 'text',
                'value' => $registrationData['father_name'] ?? '',
                'editable' => true,
                'required' => true,
            ],
            [
                'key' => 'mother_name',
                'label' => 'Mother Name',
                'type' => 'text',
                'value' => $registrationData['mother_name'] ?? '',
                'editable' => true,
                'required' => true,
            ],
            [
                'key' => 'date_of_birth',
                'label' => 'Date of Birth',
                'type' => 'date',
                'value' => $registrationData['date_of_birth'] ?? '',
                'editable' => true,
                'required' => true,
            ],
            [
                'key' => 'national_id',
                'label' => 'National ID',
                'type' => 'text',
                'value' => $registrationData['national_id'] ?? '',
                'editable' => true,
                'required' => true,
            ],
        ];

        return view('citizen.auth.complete-registration', [
            'fields' => $fields,
            'verification_session_token' => $sessionToken,
            'registrationData' => $registrationData,  // ADD THIS LINE
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Identity verification failed', ['error' => $e->getMessage()]);
        return back()->withErrors(['error' => 'Failed to process ID upload: ' . $e->getMessage()]);
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
        'password' => ['required', 'string', 'min:8'],
        'password_confirmation' => ['required_with:password', 'same:password'],
    ]);

    try {
        // Get temp ID paths
        $idFrontPath = session('temp_id_front');
        $idBackPath = session('temp_id_back');

        // Create the user
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'father_name' => $request->father_name,
            'mother_name' => $request->mother_name,
            'name' => $request->first_name . ' ' . $request->last_name,
            'date_of_birth' => $request->date_of_birth,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => User::ROLE_CITIZEN,
            'phone' => $request->phone,
            'national_id' => $request->national_id,
            'id_front_path' => $idFrontPath,
            'id_back_path' => $idBackPath,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Clear session data
        session()->forget(['registration_data', 'temp_id_front', 'temp_id_back', 'verification_session_token']);

        // Log the user in
        Auth::login($user);

        return redirect()->route('citizen.dashboard')
            ->with('success', 'Registration completed successfully! Welcome!');
            
    } catch (\Exception $e) {
        \Log::error('Registration completion failed', ['error' => $e->getMessage()]);
        return back()->withErrors(['error' => 'Registration failed: ' . $e->getMessage()]);
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
            
            $user = User::where('email', $socialUser->email)->first();
            
            if (!$user) {
                $user = User::create([
                    'name' => $socialUser->name,
                    'email' => $socialUser->email,
                    'password' => null,
                    'role' => User::ROLE_CITIZEN,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
            }
            
            Auth::login($user);
            return redirect()->route('citizen.dashboard')
                ->with('success', 'Welcome! You have successfully logged in with Google.');
                
        } catch (\Exception $e) {
            Log::error('Google login failed', ['error' => $e->getMessage()]);
            return redirect()->route('login')
                ->withErrors(['email' => 'Google login failed: ' . $e->getMessage()]);
        }
    }

    // ========== LOGOUT ==========
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }
}