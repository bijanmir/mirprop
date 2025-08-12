@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <!-- Breadcrumb -->
  <div class="flex items-center gap-2 text-sm text-gray-600 mb-4">
    <a href="{{ route('properties.index') }}" class="hover:text-gray-900">Properties</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
    </svg>
    <span>{{ $property->name }}</span>
  </div>

  <!-- Header -->
  <div class="flex items-start justify-between mb-8">
    <div>
      <h1 class="text-3xl font-bold text-gray-900">{{ $property->name }}</h1>
      <div class="flex items-center gap-4 mt-2">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
          {{ $property->type === 'residential' ? 'bg-green-100 text-green-800' : '' }}
          {{ $property->type === 'commercial' ? 'bg-blue-100 text-blue-800' : '' }}
          {{ $property->type === 'mixed' ? 'bg-purple-100 text-purple-800' : '' }}
        ">
          {{ ucfirst($property->type) }}
        </span>
        <span class="text-gray-600">{{ $property->full_address }}</span>
      </div>
    </div>
    
    <div class="flex items-center gap-3">
      <a href="{{ route('properties.edit', $property) }}" 
         class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
        Edit Property
      </a>
      <a href="{{ route('units.create', ['property' => $property]) }}" 
         class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 transition">
        Add Unit
      </a>
    </div>
  </div>

  <!-- Metrics Cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Total Units</p>
          <p class="text-2xl font-bold text-gray-900">{{ $metrics['total_units'] }}</p>
        </div>
        <div class="p-3 bg-blue-100 rounded-lg">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2h14a2 2 0 012 2v2"></path>
          </svg>
        </div>
      </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Occupied</p>
          <p class="text-2xl font-bold text-green-600">{{ $metrics['occupied_units'] }}</p>
          <p class="text-xs text-gray-500">{{ $metrics['occupancy_rate'] }}% occupancy</p>
        </div>
        <div class="p-3 bg-green-100 rounded-lg">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
        </div>
      </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Available</p>
          <p class="text-2xl font-bold text-blue-600">{{ $metrics['available_units'] }}</p>
        </div>
        <div class="p-3 bg-blue-100 rounded-lg">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
          </svg>
        </div>
      </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Monthly Revenue</p>
          <p class="text-2xl font-bold text-gray-900">${{ number_format($metrics['monthly_revenue'] / 100, 0) }}</p>
        </div>
        <div class="p-3 bg-yellow-100 rounded-lg">
          <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
          </svg>
        </div>
      </div>
    </div>
  </div>

  <!-- Units Section -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
    <div class="px-6 py-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Units</h2>
        <a href="{{ route('units.create', ['property' => $property]) }}" 
           class="text-sm text-blue-600 hover:text-blue-800 font-medium">
          Add Unit
        </a>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rent</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
          @forelse($property->units as $unit)
            <tr class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ $unit->label }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  @if($unit->beds || $unit->baths)
                    {{ $unit->beds }}br / {{ $unit->baths }}ba
                  @endif
                  @if($unit->sqft)
                    <div class="text-sm text-gray-500">{{ number_format($unit->sqft) }} sq ft</div>
                  @endif
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                @if($unit->activeLease && $unit->activeLease->primaryContact)
                  <div class="text-sm text-gray-900">{{ $unit->activeLease->primaryContact->name }}</div>
                  <div class="text-sm text-gray-500">{{ $unit->activeLease->primaryContact->email }}</div>
                @else
                  <span class="text-sm text-gray-500">—</span>
                @endif
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                @if($unit->rent_amount_cents)
                  <div class="text-sm font-medium text-gray-900">${{ number_format($unit->rent_amount_cents / 100, 2) }}</div>
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
              <td colspan="6" class="px-6 py-12 text-center">
                <div class="text-gray-400 mb-4">
                  <svg class="mx-auto h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2h14a2 2 0 012 2v2"></path>
                  </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No units yet</h3>
                <p class="text-gray-500 mb-6">Start by adding units to this property.</p>
                <a href="{{ route('units.create', ['property' => $property]) }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                  Add First Unit
                </a>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection