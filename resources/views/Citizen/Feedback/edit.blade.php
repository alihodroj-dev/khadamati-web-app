@extends('layouts.app')

@section('title', 'Edit Feedback')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <div class="mb-6">
        <a href="{{ route('citizen.feedback.show', $feedback->id) }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
            <i class="ti ti-arrow-left"></i>
            <span>Back to Feedback</span>
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden" style="border: 0.5px solid #e5e7eb;">
        
        <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-5">
            <h1 class="text-xl font-bold text-white">Edit Your Feedback</h1>
            <p class="text-blue-100 text-sm mt-1">{{ $feedback->serviceRequest->service->name }}</p>
        </div>
        
        <div class="p-6">
            
            <form method="POST" action="{{ route('citizen.feedback.update', $feedback->id) }}">
                @csrf
                @method('PUT')
                
                <!-- Rating Stars -->
                <div class="mb-6 text-center">
                    <label class="block text-sm font-medium text-gray-700 mb-3">How would you rate this service?</label>
                    <div class="flex justify-center gap-2" id="ratingStars">
                        <button type="button" data-rating="1" class="star-btn text-3xl hover:text-yellow-400 transition {{ $feedback->rating >= 1 ? 'text-yellow-400' : 'text-gray-300' }}">
                            <i class="ti {{ $feedback->rating >= 1 ? 'ti-star-filled' : 'ti-star' }}"></i>
                        </button>
                        <button type="button" data-rating="2" class="star-btn text-3xl hover:text-yellow-400 transition {{ $feedback->rating >= 2 ? 'text-yellow-400' : 'text-gray-300' }}">
                            <i class="ti {{ $feedback->rating >= 2 ? 'ti-star-filled' : 'ti-star' }}"></i>
                        </button>
                        <button type="button" data-rating="3" class="star-btn text-3xl hover:text-yellow-400 transition {{ $feedback->rating >= 3 ? 'text-yellow-400' : 'text-gray-300' }}">
                            <i class="ti {{ $feedback->rating >= 3 ? 'ti-star-filled' : 'ti-star' }}"></i>
                        </button>
                        <button type="button" data-rating="4" class="star-btn text-3xl hover:text-yellow-400 transition {{ $feedback->rating >= 4 ? 'text-yellow-400' : 'text-gray-300' }}">
                            <i class="ti {{ $feedback->rating >= 4 ? 'ti-star-filled' : 'ti-star' }}"></i>
                        </button>
                        <button type="button" data-rating="5" class="star-btn text-3xl hover:text-yellow-400 transition {{ $feedback->rating >= 5 ? 'text-yellow-400' : 'text-gray-300' }}">
                            <i class="ti {{ $feedback->rating >= 5 ? 'ti-star-filled' : 'ti-star' }}"></i>
                        </button>
                    </div>
                    <input type="hidden" name="rating" id="rating" value="{{ $feedback->rating }}" required>
                    @error('rating')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Comment -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Your Feedback (Optional)</label>
                    <textarea name="comment" rows="5" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Tell us about your experience with this service...">{{ $feedback->comment }}</textarea>
                    @error('comment')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                    Update Feedback
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const starButtons = document.querySelectorAll('.star-btn');
    const ratingInput = document.getElementById('rating');
    
    starButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            ratingInput.value = rating;
            
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