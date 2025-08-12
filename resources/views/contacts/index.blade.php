@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-3xl font-bold text-gray-900">Contacts</h1>
      <p class="text-gray-600 mt-1">Manage tenants, vendors, and other contacts</p>
    </div>
    <div class="flex items-center gap-3">
      <div class="relative">
        <button type="button" 
                id="contact-type-dropdown"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Add Contact
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
        
        <div id="contact-dropdown-menu" 
             class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
          <div class="py-1">
            <a href="{{ route('contacts.create', ['type' => 'tenant']) }}" 
               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                Add Tenant
              </div>
            </a>
            <a href="{{ route('contacts.create', ['type' => 'vendor']) }}" 
               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                Add Vendor
              </div>
            </a>
            <a href="{{ route('contacts.create', ['type' => 'owner']) }}" 
               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                Add Owner
              </div>
            </a>
            <a href="{{ route('contacts.create', ['type' => 'other']) }}" 
               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 bg-gray-500 rounded-full"></div>
                Add Other
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  @include('contacts.partials.table')
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Dropdown toggle
  const dropdown = document.getElementById('contact-type-dropdown');
  const menu = document.getElementById('contact-dropdown-menu');
  
  dropdown.addEventListener('click', function(e) {
    e.stopPropagation();
    menu.classList.toggle('hidden');
  });
  
  document.addEventListener('click', function() {
    menu.classList.add('hidden');
  });

  // Listen for toast events
  document.addEventListener('toast', function(event) {
    const { message, type } = event.detail;
    showToast(message, type);
  });

  // Listen for contacts refresh events
  document.addEventListener('contactsRefresh', function() {
    htmx.trigger('#contacts-table', 'refresh');
  });
});

function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform transition-all duration-300 ${
    type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
  }`;
  toast.textContent = message;
  
  document.body.appendChild(toast);
  
  setTimeout(() => {
    toast.style.transform = 'translateX(100%)';
    setTimeout(() => document.body.removeChild(toast), 300);
  }, 3000);
}
</script>
@endsection