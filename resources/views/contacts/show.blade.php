@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <!-- Breadcrumb -->
  <div class="flex items-center gap-2 text-sm text-gray-600 mb-4">
    <a href="{{ route('contacts.index') }}" class="hover:text-gray-900">Contacts</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
    </svg>
    <span>{{ $contact->name }}</span>
  </div>

  <!-- Header -->
  <div class="flex items-start justify-between mb-8">
    <div class="flex items-center">
      <div class="flex-shrink-0 h-16 w-16">
        <div class="h-16 w-16 rounded-full flex items-center justify-center text-white font-bold text-xl
          {{ $contact->type === 'tenant' ? 'bg-green-500' : '' }}
          {{ $contact->type === 'vendor' ? 'bg-blue-500' : '' }}
          {{ $contact->type === 'owner' ? 'bg-purple-500' : '' }}
          {{ $contact->type === 'other' ? 'bg-gray-500' : '' }}
        ">
          {{ strtoupper(substr($contact->name, 0, 2)) }}
        </div>
      </div>
      <div class="ml-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ $contact->name }}</h1>
        <div class="flex items-center gap-4 mt-2">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
            {{ $contact->type === 'tenant' ? 'bg-green-100 text-green-800' : '' }}
            {{ $contact->type === 'vendor' ? 'bg-blue-100 text-blue-800' : '' }}
            {{ $contact->type === 'owner' ? 'bg-purple-100 text-purple-800' : '' }}
            {{ $contact->type === 'other' ? 'bg-gray-100 text-gray-800' : '' }}
          ">
            {{ ucfirst($contact->type) }}
          </span>
          <span class="text-gray-600">Added {{ $contact->created_at->format('M j, Y') }}</span>
        </div>
      </div>
    </div>
    
    <div class="flex items-center gap-3">
      <a href="{{ route('contacts.edit', $contact) }}" 
         class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
        Edit Contact
      </a>
      @if($contact->type === 'tenant')
        <a href="{{ route('leases.create', ['contact' => $contact]) }}" 
           class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 transition">
          Create Lease
        </a>
      @endif
    </div>
  </div>

  <!-- Contact Information -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <div class="lg:col-span-2">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-500">Email</label>
            <div class="mt-1">
              @if($contact->email)
                <a href="mailto:{{ $contact->email }}" class="text-sm text-blue-600 hover:text-blue-800">
                  {{ $contact->email }}
                </a>
              @else
                <span class="text-sm text-gray-400">Not provided</span>
              @endif
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-500">Phone</label>
            <div class="mt-1">
              @if($contact->phone)
                <a href="tel:{{ $contact->phone }}" class="text-sm text-blue-600 hover:text-blue-800">
                  {{ $contact->phone }}
                </a>
              @else
                <span class="text-sm text-gray-400">Not provided</span>
              @endif
            </div>
          </div>

          @if($contact->address)
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-500">Address</label>
              <div class="mt-1 text-sm text-gray-900">
                @if($contact->address['line1'])
                  <div>{{ $contact->address['line1'] }}</div>
                @endif
                @if($contact->address['line2'])
                  <div>{{ $contact->address['line2'] }}</div>
                @endif
                @if($contact->address['city'] || $contact->address['state'] || $contact->address['zip'])
                  <div>
                    {{ $contact->address['city'] ?? '' }}
                    @if($contact->address['city'] && ($contact->address['state'] || $contact->address['zip'])), @endif
                    {{ $contact->address['state'] ?? '' }}
                    {{ $contact->address['zip'] ?? '' }}
                  </div>
                @endif
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Metrics -->
    <div class="space-y-6">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Activity</h3>
        
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Active Leases</span>
            <span class="text-sm font-medium text-gray-900">{{ $metrics['active_leases'] }}</span>
          </div>
          
          @if($contact->type === 'tenant')
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Total Payments</span>
              <span class="text-sm font-medium text-gray-900">${{ number_format($metrics['total_payments'] / 100, 2) }}</span>
            </div>
            
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Open Tickets</span>
              <span class="text-sm font-medium text-gray-900">{{ $metrics['open_tickets'] }}</span>
            </div>
          @endif

          @if($contact->type === 'vendor' && $contact->vendor)
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Assigned Tickets</span>
              <span class="text-sm font-medium text-gray-900">{{ $contact->vendor->assignedTickets->count() }}</span>
            </div>
            
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Status</span>
              <span class="text-sm font-medium {{ $contact->vendor->is_active ? 'text-green-600' : 'text-red-600' }}">
                {{ $contact->vendor->is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <!-- Leases Section -->
  @if($contact->leases->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Leases</h2>
      </div>
      
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property / Unit</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Term</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rent</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            @foreach($contact->leases as $lease)
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ $lease->unit->property->name }}</div>
                  <div class="text-sm text-gray-500">Unit {{ $lease->unit->label }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    {{ \Carbon\Carbon::parse($lease->start_date)->format('M j, Y') }} -
                    {{ \Carbon\Carbon::parse($lease->end_date)->format('M j, Y') }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">${{ number_format($lease->rent_amount_cents / 100, 2) }}</div>
                  <div class="text-sm text-gray-500">per month</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $lease->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $lease->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $lease->status === 'ended' ? 'bg-gray-100 text-gray-800' : '' }}
                  ">
                    {{ ucfirst($lease->status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <a href="{{ route('leases.show', $lease) }}" class="text-blue-600 hover:text-blue-800">View</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif

  <!-- Recent Payments Section -->
  @if($contact->type === 'tenant' && $contact->payments->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Recent Payments</h2>
      </div>
      
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            @foreach($contact->payments->take(10) as $payment)
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ optional($payment->posted_at)->format('M j, Y') ?? $payment->created_at->format('M j, Y') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  ${{ number_format($payment->amount_cents / 100, 2) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 uppercase">
                  {{ $payment->method }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $payment->status === 'succeeded' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $payment->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                  ">
                    {{ ucfirst($payment->status) }}
                  </span>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif

  <!-- Vendor Services Section -->
  @if($contact->type === 'vendor' && $contact->vendor && $contact->vendor->services)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Services Provided</h2>
      </div>
      
      <div class="p-6">
        <div class="flex flex-wrap gap-2">
          @foreach($contact->vendor->services as $service)
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
              {{ $service }}
            </span>
          @endforeach
        </div>
      </div>
    </div>
  @endif

  <!-- Maintenance Tickets Section -->
  @if($contact->maintenanceTickets->count() > 0 || ($contact->type === 'vendor' && $contact->vendor && $contact->vendor->assignedTickets->count() > 0))
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">
          @if($contact->type === 'vendor')
            Assigned Maintenance Tickets
          @else
            Maintenance Requests
          @endif
        </h2>
      </div>
      
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property / Unit</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            @php
              $tickets = $contact->type === 'vendor' && $contact->vendor 
                ? $contact->vendor->assignedTickets->take(10) 
                : $contact->maintenanceTickets->take(10);
            @endphp
            
            @foreach($tickets as $ticket)
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ $ticket->title }}</div>
                  <div class="text-sm text-gray-500">{{ Str::limit($ticket->description, 50) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ $ticket->property->name }}</div>
                  @if($ticket->unit)
                    <div class="text-sm text-gray-500">Unit {{ $ticket->unit->label }}</div>
                  @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $ticket->priority === 'emergency' ? 'bg-red-100 text-red-800' : '' }}
                    {{ $ticket->priority === 'high' ? 'bg-orange-100 text-orange-800' : '' }}
                    {{ $ticket->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $ticket->priority === 'low' ? 'bg-green-100 text-green-800' : '' }}
                  ">
                    {{ ucfirst($ticket->priority) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $ticket->status === 'open' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $ticket->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $ticket->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $ticket->status === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}
                  ">
                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ $ticket->created_at->format('M j, Y') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <a href="{{ route('maintenance-tickets.show', $ticket) }}" class="text-blue-600 hover:text-blue-800">View</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif
</div>
@endsection