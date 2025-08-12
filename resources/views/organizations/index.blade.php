@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Your Organizations</h1>
            <p class="text-gray-600 mt-2">Select an organization to manage or create a new one</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @forelse($organizations as $org)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $org->name }}</h3>
                            @if(auth()->user()->current_organization_id === $org->id)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Current
                                </span>
                            @endif
                        </div>
                        
                        <div class="text-sm text-gray-600 mb-4">
                            <p><strong>Currency:</strong> {{ $org->settings['currency'] ?? 'USD' }}</p>
                            <p><strong>Properties:</strong> {{ $org->properties_count ?? 0 }}</p>
                            <p><strong>Units:</strong> {{ $org->units_count ?? 0 }}</p>
                        </div>

                        <div class="flex items-center justify-between">
                            @if(auth()->user()->current_organization_id === $org->id)
                                <a href="{{ route('dashboard') }}" 
                                   class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                    Go to Dashboard →
                                </a>
                            @else
                                <form action="{{ route('organizations.switch', $org) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                        Switch to this org →
                                    </button>
                                </form>
                            @endif
                            
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('organizations.edit', $org) }}" 
                                   class="text-gray-600 hover:text-gray-800 text-sm">
                                    Edit
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <div class="text-gray-400 mb-4">
                        <svg class="mx-auto h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No organizations yet</h3>
                    <p class="text-gray-500 mb-6">Create your first organization to get started with property management.</p>
                </div>
            @endforelse
        </div>

        <div class="text-center">
            <a href="{{ route('orgs.create') }}" 
               class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create New Organization
            </a>
        </div>
    </div>
</div>
@endsection