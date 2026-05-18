@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Citizen Feedback</h1>
        <p class="text-sm text-gray-500">View and manage citizen reviews</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden" style="border: 0.5px solid #e5e7eb;">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Citizen</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rating</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comment</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($feedbacks as $feedback)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900">
                        {{ $feedback->user->name }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $feedback->serviceRequest->service->name ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex gap-0.5">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $feedback->rating)
                                    <i class="ti ti-star-filled text-yellow-400 text-sm"></i>
                                @else
                                    <i class="ti ti-star text-gray-300 text-sm"></i>
                                @endif
                            @endfor
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                        {{ $feedback->comment ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $feedback->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ route('admin.feedback.show', $feedback->id) }}" class="text-blue-600 hover:underline">
                            View →
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        No feedback submitted yet
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $feedbacks->links() }}
</div>
@endsection