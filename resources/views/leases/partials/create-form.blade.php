{{-- resources/views/leases/partials/create-form.blade.php --}}
<form 
    id="create-lease-form"
    hx-post="{{ route('leases.store') }}"
    hx-target="#create-lease-form-container"
    hx-indicator="#submit-loading"
    hx-swap="innerHTML"
    class="space-y-4"
    x-data="{ submitting: false }"
    @htmx:before-request="submitting = true"
    @htmx:after-request="submitting = false">
    
    @csrf
    
    <!-- Unit Selection -->
    <div>
        <label for="unit_id" class="block text-sm font-medium text-gray-700">Unit *</label>
        <select name="unit_id" 
                id="unit_id" 
                required 
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('unit_id') border-red-300 @enderror">
            <option value="">Select a unit</option>
            @foreach($availableUnits as $propertyName => $units)
                <optgroup label="{{ $propertyName }}">
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" 
                                {{ old('unit_id', $selectedUnitId ?? '') == $unit->id ? 'selected' : '' }}
                                data-rent="{{ $unit->rent_amount_cents ?? 0 }}">
                            {{ $unit->label }}
                            @if($unit->rent_amount_cents)
                                (${{ number_format($unit->rent_amount_cents / 100, 0) }}/mo)
                            @endif
                        </option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        @error('unit_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Unit Details Display -->
    <div id="unit-details" class="hidden">
        <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Unit Information</h3>
                    <div class="mt-1 text-sm text-blue-700" id="unit-info">
                        <!-- Unit details will be populated via JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const unitSelect = document.getElementById('unit_id');
            const unitDetails = document.getElementById('unit-details');
            const unitInfo = document.getElementById('unit-info');
            const rentAmountInput = document.getElementById('rent_amount');

            unitSelect.addEventListener('change', function() {
                const selectedOption = this.selectedOptions[0];
                if (selectedOption && selectedOption.value) {
                    const rent = selectedOption.dataset.rent || 0;
                    const unitLabel = selectedOption.textContent.trim();
                    
                    unitInfo.innerHTML = `
                        <p><strong>Unit:</strong> ${unitLabel}</p>
                        ${rent > 0 ? `<p><strong>Suggested Rent:</strong> ${(rent/100).toLocaleString()}/month</p>` : ''}
                    `;
                    
                    // Auto-populate rent amount if available
                    if (rent > 0 && !rentAmountInput.value) {
                        rentAmountInput.value = (rent / 100).toFixed(2);
                    }
                    
                    unitDetails.classList.remove('hidden');
                } else {
                    unitDetails.classList.add('hidden');
                }
            });

            // Trigger on load if unit is pre-selected
            if (unitSelect.value) {
                unitSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>

    <!-- Tenant Information -->
    <div class="border-t pt-4">
        <h4 class="text-sm font-medium text-gray-900 mb-3">Tenant Information</h4>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Contact Selection -->
            <div class="sm:col-span-2">
                <label for="primary_contact_id" class="block text-sm font-medium text-gray-700">Select Tenant *</label>
                <select name="primary_contact_id" 
                        id="primary_contact_id"
                        required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('primary_contact_id') border-red-300 @enderror">
                    <option value="">Choose a tenant</option>
                    @foreach($tenants as $contact)
                        <option value="{{ $contact->id }}" {{ old('primary_contact_id', $selectedContactId ?? '') == $contact->id ? 'selected' : '' }}>
                            {{ $contact->name }} ({{ $contact->email }})
                        </option>
                    @endforeach
                </select>
                @error('primary_contact_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                
                <div class="mt-2">
                    <a href="{{ route('contacts.create', ['type' => 'tenant', 'return_url' => url()->full()]) }}" 
                       class="text-sm text-blue-600 hover:text-blue-900">
                        + Create new tenant contact
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Lease Details -->
    <div class="border-t pt-4">
        <h4 class="text-sm font-medium text-gray-900 mb-3">Lease Terms</h4>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date *</label>
                <input type="date" 
                       name="start_date" 
                       id="start_date"
                       value="{{ old('start_date', now()->format('Y-m-d')) }}"
                       required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('start_date') border-red-300 @enderror">
                @error('start_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date *</label>
                <input type="date" 
                       name="end_date" 
                       id="end_date"
                       value="{{ old('end_date', now()->addYear()->format('Y-m-d')) }}"
                       required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('end_date') border-red-300 @enderror">
                @error('end_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Financial Details -->
    <div class="border-t pt-4">
        <h4 class="text-sm font-medium text-gray-900 mb-3">Financial Terms</h4>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="rent_amount" class="block text-sm font-medium text-gray-700">Monthly Rent *</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">$</span>
                    </div>
                    <input type="number" 
                           name="rent_amount" 
                           id="rent_amount"
                           value="{{ old('rent_amount') }}"
                           required 
                           min="0" 
                           step="0.01" 
                           class="block w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('rent_amount') border-red-300 @enderror">
                </div>
                @error('rent_amount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="deposit_amount" class="block text-sm font-medium text-gray-700">Security Deposit</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">$</span>
                    </div>
                    <input type="number" 
                           name="deposit_amount" 
                           id="deposit_amount"
                           value="{{ old('deposit_amount') }}"
                           min="0" 
                           step="0.01" 
                           class="block w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('deposit_amount') border-red-300 @enderror">
                </div>
                @error('deposit_amount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-4">
            <label for="rent_due_day" class="block text-sm font-medium text-gray-700">Rent Due Day</label>
            <select name="rent_due_day" 
                    id="rent_due_day" 
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="1" {{ old('rent_due_day', 1) == 1 ? 'selected' : '' }}>1st of the month</option>
                @for($day = 2; $day <= 28; $day++)
                    <option value="{{ $day }}" {{ old('rent_due_day') == $day ? 'selected' : '' }}>
                        {{ $day }}{{ $day === 1 ? 'st' : ($day === 2 ? 'nd' : ($day === 3 ? 'rd' : 'th')) }} of the month
                    </option>
                @endfor
            </select>
        </div>
    </div>

    <!-- Frequency -->
    <div>
        <label for="frequency" class="block text-sm font-medium text-gray-700">Payment Frequency</label>
        <select name="frequency" 
                id="frequency" 
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            <option value="monthly" {{ old('frequency', 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly</option>
            <option value="weekly" {{ old('frequency') === 'weekly' ? 'selected' : '' }}>Weekly</option>
            <option value="yearly" {{ old('frequency') === 'yearly' ? 'selected' : '' }}>Yearly</option>
        </select>
    </div>

    <!-- Additional Options -->
    <div class="border-t pt-4 space-y-3">
        <div class="flex items-center">
            <input type="checkbox" 
                   name="create_rent_charge" 
                   id="create_rent_charge"
                   value="1"
                   {{ old('create_rent_charge', true) ? 'checked' : '' }}
                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="create_rent_charge" class="ml-2 block text-sm text-gray-900">
                Create recurring rent charge
            </label>
        </div>
        
        <div class="flex items-center">
            <input type="checkbox" 
                   name="send_welcome_email" 
                   id="send_welcome_email"
                   value="1"
                   {{ old('send_welcome_email', true) ? 'checked' : '' }}
                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="send_welcome_email" class="ml-2 block text-sm text-gray-900">
                Send welcome email to tenant with portal access
            </label>
        </div>
    </div>

    <!-- Error Messages -->
    @if($errors->any())
        <div class="rounded-md bg-red-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
</form>