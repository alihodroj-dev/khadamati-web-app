<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfficeResource;
use App\Http\Resources\ServiceCategoryResource;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\UserResource;
use App\Models\Office;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Support\ServiceValidation;
use App\Support\UserOfficeAssignment;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    use ApiResponse;

    public function users(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::query()->with('office')->latest();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return $this->successResponse(
            UserResource::collection($query->get()),
            'Users retrieved successfully.'
        );
    }

    public function createUser(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in([User::ROLE_CITIZEN, User::ROLE_STAFF, User::ROLE_ADMIN])],
            'phone' => ['nullable', 'string'],
            'national_id' => ['nullable', 'string', 'unique:users,national_id'],
            'is_active' => ['sometimes', 'boolean'],
            ...UserOfficeAssignment::officeIdRules($request),
        ]);

        $user = User::create([
            ...$validated,
            'office_id' => UserOfficeAssignment::resolveOfficeId(
                $validated['role'],
                $validated['office_id'] ?? null
            ),
            'password' => Hash::make($validated['password']),
            'is_active' => $validated['is_active'] ?? true,
        ]);
        $user->load('office');

        return $this->successResponse(
            new UserResource($user),
            'User created successfully.',
            201
        );
    }

    public function updateUser(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string'],
            'national_id' => ['nullable', 'string', Rule::unique('users', 'national_id')->ignore($user->id)],
            'password' => ['sometimes', 'string', 'min:8'],
            'role' => ['sometimes', Rule::in([User::ROLE_CITIZEN, User::ROLE_STAFF, User::ROLE_ADMIN])],
            ...UserOfficeAssignment::officeIdRules($request, $user),
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        if (array_key_exists('role', $validated) || array_key_exists('office_id', $validated)) {
            $role = $validated['role'] ?? $user->role;
            $validated['office_id'] = UserOfficeAssignment::resolveOfficeId(
                $role,
                $validated['office_id'] ?? $user->office_id
            );
            $validated['role'] = $role;
        }

        $user->update($validated);
        $user->load('office');

        return $this->successResponse(
            new UserResource($user),
            'User updated successfully.'
        );
    }

    public function updateRole(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'role' => ['required', Rule::in([User::ROLE_CITIZEN, User::ROLE_STAFF, User::ROLE_ADMIN])],
            ...UserOfficeAssignment::officeIdRules($request, $user),
        ]);

        $user->update([
            'role' => $validated['role'],
            'office_id' => UserOfficeAssignment::resolveOfficeId(
                $validated['role'],
                $validated['office_id'] ?? null
            ),
        ]);
        $user->load('office');

        return $this->successResponse(
            new UserResource($user),
            'User role updated successfully.'
        );
    }

    public function updateActivation(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $user->update(['is_active' => $validated['is_active']]);
        $user->load('office');

        return $this->successResponse(
            new UserResource($user),
            'User activation updated successfully.'
        );
    }

    public function staff()
    {
        $this->authorize('viewAny', User::class);

        $staff = User::where('role', User::ROLE_STAFF)
            ->with('office')
            ->latest()
            ->get();

        return $this->successResponse(
            UserResource::collection($staff),
            'Staff users retrieved successfully.'
        );
    }

    public function services()
    {
        $services = Service::with(['category', 'office'])
            ->latest()
            ->get();

        return $this->successResponse(
            ServiceResource::collection($services),
            'Services retrieved successfully.'
        );
    }

    public function createService(Request $request)
    {
        $this->authorize('create', Service::class);

        $service = Service::create(
            ServiceValidation::validate($request, $request->user())
        );
        $service->load(['category', 'office']);

        return $this->successResponse(
            new ServiceResource($service),
            'Service created successfully.',
            201
        );
    }

    public function updateService(Request $request, Service $service)
    {
        $this->authorize('update', $service);

        $service->update(
            ServiceValidation::validate($request, $request->user(), partial: true, service: $service)
        );
        $service->load(['category', 'office']);

        return $this->successResponse(
            new ServiceResource($service),
            'Service updated successfully.'
        );
    }

    public function deleteService(Service $service)
    {
        $this->authorize('delete', $service);

        $service->delete();

        return $this->successResponse(null, 'Service deleted successfully.');
    }

    public function categories()
    {
        $categories = ServiceCategory::withCount('services')
            ->latest()
            ->get();

        return $this->successResponse(
            ServiceCategoryResource::collection($categories),
            'Service categories retrieved successfully.'
        );
    }

    public function createCategory(Request $request)
    {
        $this->authorize('create', ServiceCategory::class);

        $category = ServiceCategory::create($this->validateCategory($request));

        return $this->successResponse(
            new ServiceCategoryResource($category),
            'Service category created successfully.',
            201
        );
    }

    public function updateCategory(Request $request, ServiceCategory $serviceCategory)
    {
        $this->authorize('update', $serviceCategory);

        $serviceCategory->update($this->validateCategory($request, true));

        return $this->successResponse(
            new ServiceCategoryResource($serviceCategory),
            'Service category updated successfully.'
        );
    }

    public function deleteCategory(ServiceCategory $serviceCategory)
    {
        $this->authorize('delete', $serviceCategory);

        if ($serviceCategory->services()->exists()) {
            return $this->errorResponse('Service category has services attached.', null, 422);
        }

        $serviceCategory->delete();

        return $this->successResponse(null, 'Service category deleted successfully.');
    }

    public function offices()
    {
        $offices = Office::query()
            ->with('municipality')
            ->withCount('serviceRequests')
            ->latest()
            ->get();

        return $this->successResponse(
            OfficeResource::collection($offices),
            'Offices retrieved successfully.'
        );
    }

    public function showOffice(Office $office)
    {
        $office->load('municipality')->loadCount('serviceRequests');

        return $this->successResponse(
            new OfficeResource($office),
            'Office retrieved successfully.'
        );
    }

    public function createOffice(Request $request)
    {
        $this->authorize('create', Office::class);

        $office = Office::query()->create($this->validateOffice($request));
        $office->load('municipality');

        return $this->successResponse(
            new OfficeResource($office),
            'Office created successfully.',
            201
        );
    }

    public function updateOffice(Request $request, Office $office)
    {
        $this->authorize('update', $office);

        $office->update($this->validateOffice($request, true));
        $office->load('municipality');

        return $this->successResponse(
            new OfficeResource($office),
            'Office updated successfully.'
        );
    }

    public function deleteOffice(Office $office)
    {
        $this->authorize('delete', $office);

        if ($office->serviceRequests()->exists()) {
            return $this->errorResponse('Office has service requests attached.', null, 422);
        }

        $office->delete();

        return $this->successResponse(null, 'Office deleted successfully.');
    }

    private function validateCategory(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'name' => [$required, 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'string', 'url', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    private function validateOffice(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'municipality_id' => ['nullable', 'integer', 'exists:municipalities,id'],
            'name' => [$required, 'string', 'max:255'],
            'address' => [$required, 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'image_url' => ['nullable', 'string', 'url', 'max:2048'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'working_hours' => ['nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
