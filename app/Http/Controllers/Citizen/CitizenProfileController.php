<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CitizenProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        $deviceTokens = DeviceToken::where('user_id', $user->id)->get();
        
        return view('citizen.profile.show', compact('user', 'deviceTokens'));
    }

    public function edit()
    {
        $user = auth()->user();
        return view('citizen.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'push_notifications_enabled' => ['boolean'],
            'email_notifications_enabled' => ['boolean'],
            'sms_notifications_enabled' => ['boolean'],
        ]);

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'push_notifications_enabled' => $request->boolean('push_notifications_enabled'),
            'email_notifications_enabled' => $request->boolean('email_notifications_enabled'),
            'sms_notifications_enabled' => $request->boolean('sms_notifications_enabled'),
        ]);

        return redirect()->route('citizen.profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }
}