@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-3xl font-bold text-gray-900">Properties</h1>
      <p class="text-gray-600 mt-1">Manage your real estate portfolio</p>
    </div>
    <div class="flex items-center gap-3">
      <a href="{{ route('properties.create') }}" 
         class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Add Property
      </a>
    </div>
  </div>

  @include('properties.partials.table')
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Listen for toast events
  document.addEventListener('toast', function(event) {
    const { message, type } = event.detail;
    showToast(message, type);
  });

  // Listen for properties refresh events
  document.addEventListener('propertiesRefresh', function() {
    htmx.trigger('#properties-table', 'refresh');
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