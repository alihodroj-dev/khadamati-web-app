@extends('layouts.app')

@section('title', 'My Requests')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">My Service Requests</h1>
        <p class="text-gray-500 mt-1">Track and manage your submitted requests</p>
    </div>

    <!-- Status Filter Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex gap-4 flex-wrap">
                <a href="{{ route('citizen.requests.index') }}" 
                   class="px-3 py-2 text-sm font-medium {{ !request('status') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                    All
                </a>
                <a href="{{ route('citizen.requests.index', ['status' => 'pending']) }}" 
                   class="px-3 py-2 text-sm font-medium {{ request('status') == 'pending' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                    Pending
                </a>
                <a href="{{ route('citizen.requests.index', ['status' => 'under_review']) }}" 
                   class="px-3 py-2 text-sm font-medium {{ request('status') == 'under_review' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                    Under Review
                </a>
                <a href="{{ route('citizen.requests.index', ['status' => 'approved']) }}" 
                   class="px-3 py-2 text-sm font-medium {{ request('status') == 'approved' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                    Approved
                </a>
                <a href="{{ route('citizen.requests.index', ['status' => 'completed']) }}" 
                   class="px-3 py-2 text-sm font-medium {{ request('status') == 'completed' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                    Completed
                </a>
                <a href="{{ route('citizen.requests.index', ['status' => 'rejected']) }}" 
                   class="px-3 py-2 text-sm font-medium {{ request('status') == 'rejected' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                    Rejected
                </a>
                <a href="{{ route('citizen.requests.index', ['status' => 'cancelled']) }}" 
                   class="px-3 py-2 text-sm font-medium {{ request('status') == 'cancelled' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                    Cancelled
                </a>
            </nav>
        </div>
    </div>

    @if($requests->count() > 0)
        <div class="space-y-4">
            @foreach($requests as $req)
                <div class="bg-white rounded-xl p-5 shadow-sm hover:shadow-md transition" style="border: 0.5px solid #e5e7eb;">
                    
                    <div class="flex flex-wrap justify-between items-start gap-4">
                        <!-- Request Info -->
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2 flex-wrap">
                                <h3 class="font-semibold text-gray-900">{{ $req->service->name ?? 'Unknown Service' }}</h3>
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($req->status == 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($req->status == 'under_review') bg-blue-100 text-blue-800
                                    @elseif($req->status == 'approved') bg-green-100 text-green-800
                                    @elseif($req->status == 'completed') bg-emerald-100 text-emerald-800
                                    @elseif($req->status == 'rejected') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                                </span>
                            </div>
                            
                            <p class="text-sm text-gray-500 mb-1">Reference: {{ $req->reference_number }}</p>
                            <p class="text-sm text-gray-500">Submitted: {{ $req->created_at->format('M d, Y h:i A') }}</p>
                            
                            @if($req->office)
                                <p class="text-sm text-gray-500 mt-1">
                                    <i class="ti ti-building"></i> {{ $req->office->name }}
                                </p>
                            @endif
                        </div>
                        
                        <!-- Fee & Actions -->
                        <div class="text-right">
                            @if($req->payment)
                                <p class="text-lg font-bold text-blue-900">${{ number_format($req->payment->amount, 2) }}</p>
                                <p class="text-xs text-gray-500 mb-2">
                                    Payment: 
                                    <span class="{{ $req->payment->status == 'paid' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ ucfirst($req->payment->status) }}
                                    </span>
                                </p>
                            @endif
                            
                            <div class="flex gap-2 mt-2">
                                <a href="{{ route('citizen.requests.show', $req->id) }}" 
                                   class="px-3 py-1.5 text-sm bg-blue-900 text-white rounded-lg hover:bg-blue-800 transition">
                                    View Details
                                </a>
                                
                                @if(in_array($req->status, ['pending', 'under_review']))
                                    <form method="POST" action="{{ route('citizen.requests.cancel', $req->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="px-3 py-1.5 text-sm border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition"
                                                onclick="return confirm('Are you sure you want to cancel this request?')">
                                            Cancel
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="mt-6">
            {{ $requests->withQueryString()->links() }}
        </div>
        
    @else
        <div class="bg-white rounded-xl p-12 text-center" style="border: 0.5px solid #e5e7eb;">
            <i class="ti ti-clipboard-list text-gray-300 text-5xl mb-3 block"></i>
            <p class="text-gray-500 mb-2">No service requests found</p>
            <p class="text-sm text-gray-400">Start by requesting a service</p>
            <a href="{{ route('citizen.services.index') }}" class="inline-block mt-4 px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800 transition">
                Browse Services →
            </a>
        </div>
    @endif

</div>
@endsection