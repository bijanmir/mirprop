@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
  <div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
      <a href="{{ route('contacts.index') }}" class="hover:text-gray-900">Contacts</a>
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
      </svg>
      <span>Add {{ ucfirst($type) }}</span>
    </div>
    <h1 class="text-3xl font-bold text-gray-900">Add New {{ ucfirst($type) }}</h1>
    <p class="text-gray-600 mt-1">Add a {{ $type }} to your contact list</p>
  </div>

  <div class="bg-white shadow-sm rounded-xl border border-gray-200">
    <form action="{{ route('contacts.store') }}" method="POST" class="p-6 space-y-6">
      @csrf
      <input type="hidden" name="type" value="{{ $type }}">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
          <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
            Contact Type *
          </label>
          <select id="type" 
                  name="type" 
                  required
                  class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-300 @enderror">
            <option value="tenant" {{ $type === 'tenant' ? 'selected' : '' }}>Tenant</option>
            <option value="vendor" {{ $type === 'vendor' ? 'selected' : '' }}>Vendor</option>
            <option value="owner" {{ $type === 'owner' ? 'selected' : '' }}>Owner</option>
            <option value="other" {{ $type === 'other' ? 'selected' : '' }}>Other</option>
          </select>
          @error('type')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div class="md:col-span-2">
          <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
            Full Name *
          </label>
          <input type="text" 
                 id="name" 
                 name="name" 
                 value="{{ old('name') }}"
                 required
                 placeholder="Enter full name"
                 class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-300 @enderror">
          @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
            Email Address
          </label>
          <input type="email" 
                 id="email" 
                 name="email" 
                 value="{{ old('email') }}"
                 placeholder="contact@example.com"
                 class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-300 @enderror">
          @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
            Phone Number
          </label>
          <input type="tel" 
                 id="phone" 
                 name="phone" 
                 value="{{ old('phone') }}"
                 placeholder="(555) 123-4567"
                 class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-300 @enderror">
          @error('phone')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>
      </div>

      <div class="border-t pt-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Address Information</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="md:col-span-2">
            <label for="address_line1" class="block text-sm font-medium text-gray-700 mb-2">
              Street Address
            </label>
            <input type="text" 
                   id="address_line1" 
                   name="address[line1]" 
                   value="{{ old('address.line1') }}"
                   placeholder="123 Main Street"
                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('address.line1') border-red-300 @enderror">
            @error('address.line1')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div class="md:col-span-2">
            <label for="address_line2" class="block text-sm font-medium text-gray-700 mb-2">
              Address Line 2
            </label>
            <input type="text" 
                   id="address_line2" 
                   name="address[line2]" 
                   value="{{ old('address.line2') }}"
                   placeholder="Apt, suite, unit, building, floor, etc."
                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('address.line2') border-red-300 @enderror">
            @error('address.line2')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="address_city" class="block text-sm font-medium text-gray-700 mb-2">
              City
            </label>
            <input type="text" 
                   id="address_city" 
                   name="address[city]" 
                   value="{{ old('address.city') }}"
                   placeholder="Austin"
                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('address.city') border-red-300 @enderror">
            @error('address.city')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="address_state" class="block text-sm font-medium text-gray-700 mb-2">
              State
            </label>
            <input type="text" 
                   id="address_state" 
                   name="address[state]" 
                   value="{{ old('address.state') }}"
                   maxlength="2"
                   placeholder="TX"
                   style="text-transform: uppercase"
                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('address.state') border-red-300 @enderror">
            @error('address.state')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="address_zip" class="block text-sm font-medium text-gray-700 mb-2">
              ZIP Code
            </label>
            <input type="text" 
                   id="address_zip" 
                   name="address[zip]" 
                   value="{{ old('address.zip') }}"
                   placeholder="78701"
                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('address.zip') border-red-300 @enderror">
            @error('address.zip')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>
        </div>
      </div>

      <div class="flex items-center justify-between pt-6 border-t">
        <a href="{{ route('contacts.index') }}" 
           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
          Cancel
        </a>
        
        <button type="submit" 
                class="px-6 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
          Create {{ ucfirst($type) }}
        </button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const stateInput = document.getElementById('address_state');
  
  stateInput.addEventListener('input', function() {
    this.value = this.value.toUpperCase();
  });

  // Auto-update form title when type changes
  const typeSelect = document.getElementById('type');
  const title = document.querySelector('h1');
  
  typeSelect.addEventListener('change', function() {
    const newType = this.value;
    title.textContent = `Add New ${newType.charAt(0).toUpperCase() + newType.slice(1)}`;
  });
});
</script>
@endsection