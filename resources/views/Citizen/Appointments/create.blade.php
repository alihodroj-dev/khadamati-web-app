@extends('layouts.app')

@section('title', 'Book Appointment')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <div class="mb-6">
        <a href="{{ route('citizen.requests.show', $serviceRequest->id) }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
            <i class="ti ti-arrow-left"></i>
            <span>Back to Request</span>
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden" style="border: 0.5px solid #e5e7eb;">
        
        <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-5">
            <h1 class="text-xl font-bold text-white">Book an Appointment</h1>
            <p class="text-blue-100 text-sm mt-1">{{ $serviceRequest->service->name }}</p>
        </div>
        
        <div class="p-6">
            
            <form method="POST" action="{{ route('citizen.appointments.store', $serviceRequest->id) }}" id="appointmentForm">
                @csrf
                
                <!-- Service Info -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Request Reference</p>
                            <p class="font-medium">{{ $serviceRequest->reference_number }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Service Fee</p>
                            <p class="font-bold text-blue-900">${{ number_format($serviceRequest->service->base_fee, 2) }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Appointment Date -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Date</label>
                    <input type="date" 
                           name="appointment_date" 
                           id="appointment_date"
                           min="{{ date('Y-m-d') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                
                <!-- Staff Member -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Staff Member</label>
                    <select name="staff_id" id="staff_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Select a staff member first</option>
                    </select>
                </div>
                
                <!-- Available Time Slots -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Time</label>
                    <div id="timeSlots" class="grid grid-cols-3 gap-2">
                        <p class="text-gray-500 text-sm col-span-3">Select a date and staff member first</p>
                    </div>
                </div>
                
                <!-- Notes -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Additional Notes (Optional)</label>
                    <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Any special requirements or information..."></textarea>
                </div>
                
                <input type="hidden" name="appointment_time" id="appointment_time" required>
                
                <button type="submit" 
                        id="submitBtn"
                        class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition"
                        disabled>
                    Confirm Appointment
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const dateInput = document.getElementById('appointment_date');
    const staffSelect = document.getElementById('staff_id');
    const timeSlotsDiv = document.getElementById('timeSlots');
    const timeInput = document.getElementById('appointment_time');
    const submitBtn = document.getElementById('submitBtn');
    
    let availableSlots = [];
    
    // Load staff members when date is selected
    dateInput.addEventListener('change', function() {
        if (this.value) {
            loadStaffMembers();
        }
    });
    
    function loadStaffMembers() {
    const date = dateInput.value;
    
    if (!date) return;
    
    fetch(`/citizen/appointments/availability/staff?date=${date}`)
        .then(response => response.json())
        .then(data => {
            console.log('Staff data received:', data);
            staffSelect.innerHTML = '<option value="">Select a staff member</option>';
            
            if (!data || data.length === 0) {
                staffSelect.innerHTML = '<option value="">No staff available on this date</option>';
                timeSlotsDiv.innerHTML = '<p class="text-gray-500 text-sm col-span-3">No staff available</p>';
                return;
            }
            
            data.forEach(staff => {
                const option = document.createElement('option');
                option.value = staff.id;
                option.textContent = `${staff.name} (${staff.office_name || 'Office'})`;
                staffSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading staff:', error);
            staffSelect.innerHTML = '<option value="">Error loading staff</option>';
        });
}
    
    // Load time slots when staff is selected
    staffSelect.addEventListener('change', function() {
        if (this.value && dateInput.value) {
            loadTimeSlots();
        }
    });
    
    function loadTimeSlots() {
    const staffId = staffSelect.value;
    const date = dateInput.value;
    
    if (!staffId || !date) return;
    
    fetch(`/citizen/appointments/availability/${staffId}?date=${date}`)
        .then(response => response.json())
        .then(data => {
            console.log('Time slots received:', data);
            availableSlots = data;
            timeSlotsDiv.innerHTML = '';
            
            if (!data || data.length === 0) {
                timeSlotsDiv.innerHTML = '<p class="text-gray-500 text-sm col-span-3">No available time slots for this date</p>';
                submitBtn.disabled = true;
                return;
            }
            
            data.forEach(slot => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-blue-50 hover:border-blue-500 transition';
                btn.textContent = slot.display;
                btn.onclick = () => selectTime(slot.time, btn);
                timeSlotsDiv.appendChild(btn);
            });
            
            submitBtn.disabled = true;
        })
        .catch(error => {
            console.error('Error loading time slots:', error);
            timeSlotsDiv.innerHTML = '<p class="text-gray-500 text-sm col-span-3">Error loading time slots</p>';
        });
}
    
    function selectTime(time, button) {
        // Remove selected class from all buttons
        document.querySelectorAll('#timeSlots button').forEach(btn => {
            btn.classList.remove('bg-blue-900', 'text-white', 'border-blue-900');
            btn.classList.add('border-gray-300', 'text-gray-700');
        });
        
        // Add selected class to clicked button
        button.classList.remove('border-gray-300', 'text-gray-700');
        button.classList.add('bg-blue-900', 'text-white', 'border-blue-900');
        
        // Set the time input
        timeInput.value = time;
        submitBtn.disabled = false;
    }
</script>
@endsection