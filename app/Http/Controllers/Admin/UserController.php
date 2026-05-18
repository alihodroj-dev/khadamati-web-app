<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\User;
use App\Support\AdminFormInput;
use App\Support\UserOfficeAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        $users = User::query()->with('office')->latest()->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $offices = Office::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.users.create', compact('offices'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedUserData($request, creating: true);

        User::query()->create([
            ...$validated,
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $offices = Office::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'offices'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $this->validatedUserData($request, creating: false, user: $user);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function show($id)
    {
        $user = User::query()->with('office')->findOrFail($id);

        return view('admin.users.show', compact('user'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedUserData(Request $request, bool $creating, ?User $user = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'password' => [$creating ? 'required' : 'nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:50'],
            'national_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('users', 'national_id')->ignore($user?->id),
            ],
            'role' => [
                'required',
                Rule::in([User::ROLE_CITIZEN, User::ROLE_STAFF, User::ROLE_ADMIN]),
            ],
            'is_active' => ['required', Rule::in(['0', '1', 0, 1, true, false])],
            'office_id' => ['nullable', 'exists:offices,id'],
        ]);

        if ($validated['role'] === User::ROLE_STAFF && empty($validated['office_id'])) {
            throw ValidationException::withMessages([
                'office_id' => 'Staff users must be assigned to an office.',
            ]);
        }

        if ($validated['role'] === User::ROLE_ADMIN && !empty($validated['office_id'])) {
            $validated['office_id'] = null;
        }

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'national_id' => $validated['national_id'] ?? null,
            'role' => $validated['role'],
            'office_id' => $validated['role'] === User::ROLE_STAFF ? $validated['office_id'] : null,
            'is_active' => AdminFormInput::boolean($validated['is_active']),
        ];

        if (! empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        return $data;
    }
}
