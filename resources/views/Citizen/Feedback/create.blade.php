@extends('layouts.app')

@section('title', 'Submit Feedback')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <div class="mb-6">
        <a href="{{ route('citizen.requests.show', $serviceRequest->id) }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
            <i class="ti ti-arrow-left"></i>
            <span>Back to Request</span>
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden" style="border: 0.5px solid #e5e7eb;">
        
        <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-5">
            <h1 class="text-xl font-bold text-white">Rate Your Experience</h1>
            <p class="text-blue-100 text-sm mt-1">{{ $serviceRequest->service->name }}</p>
        </div>
        
        <div class="p-6">
            
            <form method="POST" action="{{ route('citizen.feedback.store', $serviceRequest->id) }}">
                @csrf
                
                <!-- Rating Stars -->
                <div class="mb-6 text-center">
                    <label class="block text-sm font-medium text-gray-700 mb-3">How would you rate this service?</label>
                    <div class="flex justify-center gap-2" id="ratingStars">
                        <button type="button" data-rating="1" class="star-btn text-3xl text-gray-300 hover:text-yellow-400 transition">
                            <i class="ti ti-star"></i>
                        </button>
                        <button type="button" data-rating="2" class="star-btn text-3xl text-gray-300 hover:text-yellow-400 transition">
                            <i class="ti ti-star"></i>
                        </button>
                        <button type="button" data-rating="3" class="star-btn text-3xl text-gray-300 hover:text-yellow-400 transition">
                            <i class="ti ti-star"></i>
                        </button>
                        <button type="button" data-rating="4" class="star-btn text-3xl text-gray-300 hover:text-yellow-400 transition">
                            <i class="ti ti-star"></i>
                        </button>
                        <button type="button" data-rating="5" class="star-btn text-3xl text-gray-300 hover:text-yellow-400 transition">
                            <i class="ti ti-star"></i>
                        </button>
                    </div>
                    <input type="hidden" name="rating" id="rating" required>
                    @error('rating')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Comment -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Your Feedback (Optional)</label>
                    <textarea name="comment" rows="5" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Tell us about your experience with this service..."></textarea>
                    @error('comment')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Info Box -->
                <div class="bg-blue-50 rounded-lg p-4 mb-6">
                    <p class="text-sm text-blue-800">
                        <i class="ti ti-info-circle"></i> Your feedback helps us improve our services and helps other citizens make informed decisions.
                    </p>
                </div>
                
                <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                    Submit Feedback
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const starButtons = document.querySelectorAll('.star-btn');
    const ratingInput = document.getElementById('rating');
    let currentRating = 0;
    
    starButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            currentRating = rating;
            ratingInput.value = rating;
            
            // Update star colors
            starButtons.forEach((star, index) => {
                const starIcon = star.querySelector('i');
                if (index < rating) {
                    starIcon.classList.remove('ti-star');
                    starIcon.classList.add('ti-star-filled');
                    star.classList.remove('text-gray-300');
                    star.classList.add('text-yellow-400');
                } else {
                    starIcon.classList.remove('ti-star-filled');
                    starIcon.classList.add('ti-star');
                    star.classList.remove('text-yellow-400');
                    star.classList.add('text-gray-300');
                }
            });
        });
    });
</script>
@endsection