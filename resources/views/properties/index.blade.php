@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Properties</h1>
    <div class="flex items-center gap-3">
      <a href="{{ route('properties.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
        Add Property
      </a>
      <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to Dashboard</a>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($properties as $property)
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
        <div class="p-6">
          <div class="flex items-start justify-between mb-3">
            <h3 class="text-lg font-semibold text-gray-900">{{ $property->name }}</h3>
            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $property->type === 'residential' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
              {{ ucfirst($property->type) }}
            </span>
          </div>
          
          <div class="text-sm text-gray-600 mb-4">
            <p>{{ $property->address_line1 }}</p>
            <p>{{ $property->city }}, {{ $property->state }} {{ $property->zip }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="text-center p-3 bg-gray-50 rounded-lg">
              <div class="text-2xl font-bold text-gray-900">{{ $property->units_count }}</div>
              <div class="text-xs text-gray-500">Total Units</div>
            </div>
            <div class="text-center p-3 bg-gray-50 rounded-lg">
              <div class="text-2xl font-bold text-green-600">{{ $property->occupied_units_count }}</div>
              <div class="text-xs text-gray-500">Occupied</div>
            </div>
          </div>

          <div class="flex items-center justify-between">
            <div class="text-sm">
              <span class="text-gray-500">Occupancy:</span>
              <span class="font-medium">
                {{ $property->units_count > 0 ? round(($property->occupied_units_count / $property->units_count) * 100) : 0 }}%
              </span>
            </div>
            <a href="{{ route('properties.show', $property) }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm">
              View Details →
            </a>
          </div>
        </div>
      </div>
    @empty
      <div class="col-span-full">
        <div class="text-center py-12">
          <div class="text-gray-400 mb-4">
            <svg class="mx-auto h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No properties yet</h3>
          <p class="text-gray-500 mb-6">Get started by adding your first property to the system.</p>
          <a href="{{ route('properties.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition">
            Add Your First Property
          </a>
        </div>
      </div>
    @endforelse
  </div>

  @if($properties->hasPages())
    <div class="mt-8">
      {{ $properties->links() }}
    </div>
  @endif
</div>
@endsection