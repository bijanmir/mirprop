@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-3xl font-bold text-gray-900">Units</h1>
      @if(isset($property))
        <p class="text-gray-600 mt-1">{{ $property->name }}</p>
      @endif
    </div>
    <div class="flex items-center gap-3">
      @if(isset($property))
        <a href="{{ route('units.create', ['property' => $property]) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
          Add Unit
        </a>
        <a href="{{ route('properties.show', $property) }}" class="text-sm text-gray-600 hover:text-gray-900">Back to Property</a>
      @else
        <a href="{{ route('units.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
          Add Unit
        </a>
        <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to Dashboard</a>
      @endif
    </div>
  </div>

  <div class="bg-white shadow-sm rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rent</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
          @forelse($units as $unit)
            <tr class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ $unit->label }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ $unit->property->name }}</div>
                <div class="text-sm text-gray-500">{{ $unit->property->city }}, {{ $unit->property->state }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  @if($unit->bedrooms || $unit->bathrooms)
                    {{ $unit->bedrooms }}br / {{ $unit->bathrooms }}ba
                  @endif
                  @if($unit->square_feet)
                    <div class="text-sm text-gray-500">{{ number_format($unit->square_feet) }} sq ft</div>
                  @endif
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                @if($unit->currentLease && $unit->currentLease->primaryContact)
                  <div class="text-sm text-gray-900">{{ $unit->currentLease->primaryContact->name }}</div>
                  <div class="text-sm text-gray-500">{{ $unit->currentLease->primaryContact->email }}</div>
                @else
                  <span class="text-sm text-gray-500">—</span>
                @endif
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                @if($unit->currentLease)
                  <div class="text-sm font-medium text-gray-900">${{ number_format($unit->currentLease->rent_amount_cents / 100, 2) }}</div>
                  <div class="text-sm text-gray-500">per month</div>
                @else
                  <span class="text-sm text-gray-500">—</span>
                @endif
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                  {{ $unit->status === 'occupied' ? 'bg-green-100 text-green-800' : '' }}
                  {{ $unit->status === 'available' ? 'bg-blue-100 text-blue-800' : '' }}
                  {{ $unit->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : '' }}
                  {{ $unit->status === 'unavailable' ? 'bg-gray-100 text-gray-800' : '' }}
                ">
                  {{ ucfirst($unit->status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <div class="flex items-center gap-2">
                  <a href="{{ route('units.show', $unit) }}" class="text-blue-600 hover:text-blue-800">View</a>
                  <a href="{{ route('units.edit', $unit) }}" class="text-gray-600 hover:text-gray-800">Edit</a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-6 py-12 text-center">
                <div class="text-gray-400 mb-4">
                  <svg class="mx-auto h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2h14a2 2 0 012 2v2"></path>
                  </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No units found</h3>
                <p class="text-gray-500 mb-6">Start by adding units to your properties.</p>
                @if(isset($property))
                  <a href="{{ route('units.create', ['property' => $property]) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition">
                    Add First Unit
                  </a>
                @else
                  <a href="{{ route('units.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition">
                    Add First Unit
                  </a>
                @endif
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    
    @if($units->hasPages())
      <div class="px-6 py-4 border-t border-gray-200">
        {{ $units->links() }}
      </div>
    @endif
  </div>
</div>
@endsection