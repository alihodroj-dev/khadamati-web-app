@php
    $selectedRole = old('role', $user->role ?? 'citizen');
    $selectedOfficeId = old('office_id', $user->office_id ?? '');
@endphp

<div class="mb-6" id="office-assignment-field" style="{{ $selectedRole === 'staff' ? '' : 'display:none;' }}">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Assigned Office <span class="text-red-500">*</span>
    </label>
    <select
        name="office_id"
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
    >
        <option value="">— Select office —</option>
        @foreach($offices as $office)
            <option value="{{ $office->id }}" @selected((string) $selectedOfficeId === (string) $office->id)>
                {{ $office->name }}
            </option>
        @endforeach
    </select>
    <p class="mt-1 text-xs text-gray-500">Required for staff users. Staff can only manage data for this office.</p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleSelect = document.querySelector('select[name="role"]');
        const officeField = document.getElementById('office-assignment-field');

        if (!roleSelect || !officeField) {
            return;
        }

        roleSelect.addEventListener('change', function () {
            officeField.style.display = roleSelect.value === 'staff' ? '' : 'none';
        });
    });
</script>
