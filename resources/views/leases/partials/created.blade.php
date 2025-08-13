{{-- resources/views/leases/partials/created.blade.php --}}
<div class="bg-green-50 border border-green-200 rounded-md p-4">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium text-green-800">
                Lease Created Successfully
            </h3>
            <div class="mt-2 text-sm text-green-700">
                <p>
                    Lease for <strong>{{ $lease->primaryContact->name }}</strong> 
                    at <strong>{{ $lease->unit->property->name }} - {{ $lease->unit->label }}</strong> 
                    has been created.
                </p>
                <div class="mt-3 flex space-x-3">
                    <a href="{{ route('leases.show', $lease) }}" 
                       class="text-green-800 font-medium hover:text-green-900">
                        View Lease →
                    </a>
                    <button 
                        hx-get="{{ route('leases.index') }}"
                        hx-target="#lease-table-container"
                        class="text-green-800 font-medium hover:text-green-900">
                        Refresh List
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Close modal after successful creation
    window.dispatchEvent(new CustomEvent('close-modal'));
</script>