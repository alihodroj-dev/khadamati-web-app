@php
    use App\Support\AdminFormInput;

    $service = $service ?? null;
    $requiredDocumentsValue = old(
        'required_documents',
        AdminFormInput::formatRequiredDocumentsForForm($service?->required_documents)
    );
@endphp

<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-2">Service Category</label>
    <select name="service_category_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
        <option value="" disabled {{ old('service_category_id', $service?->service_category_id) ? '' : 'selected' }}>Select a category...</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected((string) old('service_category_id', $service?->service_category_id) === (string) $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    @error('service_category_id')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-2">Office</label>
    <select name="office_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
        <option value="" @selected(old('office_id', $service?->office_id) === null || old('office_id', $service?->office_id) === '')>
            Global (all offices)
        </option>
        @foreach($offices as $office)
            <option value="{{ $office->id }}" @selected((string) old('office_id', $service?->office_id) === (string) $office->id)>
                {{ $office->name }}
            </option>
        @endforeach
    </select>
    <p class="mt-1 text-xs text-gray-500">Leave as global for services available at every office.</p>
    @error('office_id')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<x-input label="Service Name" name="name" value="{{ old('name', $service?->name) }}" placeholder="e.g. Passport Renewal" />
<x-image-url-field :value="old('image_url', $service?->image_url)" />
<x-input label="Description" name="description" value="{{ old('description', $service?->description) }}" placeholder="Briefly describe the service..." />

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-input label="Price (base fee)" name="base_fee" type="number" step="0.01" value="{{ old('base_fee', $service?->base_fee) }}" />
    <x-input label="Duration (processing days)" name="estimated_processing_days" type="number" value="{{ old('estimated_processing_days', $service?->estimated_processing_days) }}" />
</div>

<x-input
    label="Required Documents"
    name="required_documents"
    value="{{ $requiredDocumentsValue }}"
    placeholder="e.g. national_id, proof_of_address"
/>
<p class="text-xs text-gray-500 -mt-4 mb-4">Comma-separated keys; stored as normalized document definitions.</p>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Requires Appointment</label>
        <select name="requires_appointment" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="1" @selected((string) old('requires_appointment', $service?->requires_appointment ? '1' : '0') === '1')>Yes</option>
            <option value="0" @selected((string) old('requires_appointment', $service?->requires_appointment ? '1' : '0') === '0')>No</option>
        </select>
    </div>

    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
        <select name="is_active" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="1" @selected((string) old('is_active', $service?->is_active ? '1' : '0') === '1')>Active</option>
            <option value="0" @selected((string) old('is_active', $service?->is_active ? '1' : '0') === '0')>Inactive</option>
        </select>
    </div>
</div>
