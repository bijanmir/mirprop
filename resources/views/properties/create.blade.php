@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
  <div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
      <a href="{{ route('properties.index') }}" class="hover:text-gray-900">Properties</a>
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
      </svg>
      <span>Add Property</span>
    </div>
    <h1 class="text-3xl font-bold text-gray-900">Add New Property</h1>
    <p class="text-gray-600 mt-1">Add a property to your portfolio</p>
  </div>

  <div class="bg-white shadow-sm rounded-xl border border-gray-200">
    <form action="{{ route('properties.store') }}" method="POST" class="p-6 space-y-6">
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
          <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
            Property Name *
          </label>
          <input type="text" 
                 id="name" 
                 name="name" 
                 value="{{ old('name') }}"
                 required
                 placeholder="e.g., Maple Heights Apartments"
                 class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-300 @enderror">
          @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
            Property Type *
          </label>
          <select id="type" 
                  name="type" 
                  required
                  class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-300 @enderror">
            <option value="">Select type...</option>
            <option value="residential" {{ old('type') === 'residential' ? 'selected' : '' }}>Residential</option>
            <option value="commercial" {{ old('type') === 'commercial' ? 'selected' : '' }}>Commercial</option>
            <option value="mixed" {{ old('type') === 'mixed' ? 'selected' : '' }}>Mixed Use</option>
          </select>
          @error('type')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>
      </div>

      <div class="border-t pt-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Address Information</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="md:col-span-2">
            <label for="address_line1" class="block text-sm font-medium text-gray-700 mb-2">
              Street Address *
            </label>
            <input type="text" 
                   id="address_line1" 
                   name="address_line1" 
                   value="{{ old('address_line1') }}"
                   required
                   placeholder="123 Main Street"
                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('address_line1') border-red-300 @enderror">
            @error('address_line1')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div class="md:col-span-2">
            <label for="address_line2" class="block text-sm font-medium text-gray-700 mb-2">
              Address Line 2
            </label>
            <input type="text" 
                   id="address_line2" 
                   name="address_line2" 
                   value="{{ old('address_line2') }}"
                   placeholder="Apt, suite, unit, building, floor, etc."
                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('address_line2') border-red-300 @enderror">
            @error('address_line2')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
              City *
            </label>
            <input type="text" 
                   id="city" 
                   name="city" 
                   value="{{ old('city') }}"
                   required
                   placeholder="Austin"
                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('city') border-red-300 @enderror">
            @error('city')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="state" class="block text-sm font-medium text-gray-700 mb-2">
              State *
            </label>
            <input type="text" 
                   id="state" 
                   name="state" 
                   value="{{ old('state') }}"
                   required
                   maxlength="2"
                   placeholder="TX"
                   style="text-transform: uppercase"
                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('state') border-red-300 @enderror">
            @error('state')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="zip" class="block text-sm font-medium text-gray-700 mb-2">
              ZIP Code *
            </label>
            <input type="text" 
                   id="zip" 
                   name="zip" 
                   value="{{ old('zip') }}"
                   required
                   placeholder="78701"
                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('zip') border-red-300 @enderror">
            @error('zip')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="country" class="block text-sm font-medium text-gray-700 mb-2">
              Country
            </label>
            <input type="text" 
                   id="country" 
                   name="country" 
                   value="{{ old('country', 'US') }}"
                   maxlength="2"
                   placeholder="US"
                   style="text-transform: uppercase"
                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('country') border-red-300 @enderror">
            @error('country')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>
        </div>
      </div>

      <div class="flex items-center justify-between pt-6 border-t">
        <a href="{{ route('properties.index') }}" 
           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
          Cancel
        </a>
        
        <button type="submit" 
                class="px-6 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
          Create Property
        </button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const stateInput = document.getElementById('state');
  const countryInput = document.getElementById('country');
  
  [stateInput, countryInput].forEach(input => {
    input.addEventListener('input', function() {
      this.value = this.value.toUpperCase();
    });
  });
});
</script>
@endsection