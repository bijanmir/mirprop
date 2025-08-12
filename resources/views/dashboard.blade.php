<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Metrics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Units -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600">Total Units</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $metrics['total_units'] }}</p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-full">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <span class="text-green-600 font-medium">{{ $metrics['occupied_units'] }}</span>
                            <span class="text-gray-500 ml-1">occupied</span>
                            <span class="text-gray-400 mx-2">|</span>
                            <span class="text-yellow-600 font-medium">{{ $metrics['vacant_units'] }}</span>
                            <span class="text-gray-500 ml-1">available</span>
                        </div>
                    </div>
                </div>

                <!-- Occupancy Rate -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600">Occupancy Rate</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $metrics['occupancy_rate'] }}%</p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-full">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: {{ $metrics['occupancy_rate'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Collection Rate -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600">Collection Rate</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $metrics['collection_rate'] }}%</p>
                            </div>
                            <div class="p-3 bg-emerald-100 rounded-full">
                                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <span class="text-gray-600">${{ number_format($metrics['rent_collected'] / 100, 2) }}</span>
                            <span class="text-gray-500 ml-1">collected this month</span>
                        </div>
                    </div>
                </div>

                <!-- Open Maintenance -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600">Open Maintenance</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $metrics['open_tickets'] }}</p>
                            </div>
                            <div class="p-3 bg-orange-100 rounded-full">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            @if($metrics['emergency_tickets'] > 0)
                                <span class="text-red-600 font-medium">{{ $metrics['emergency_tickets'] }}</span>
                                <span class="text-gray-500 ml-1">emergency</span>
                            @else
                                <span class="text-gray-500">No emergency tickets</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Leases -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Leases</h3>
                        <div class="space-y-4">
                            @forelse($recentLeases as $lease)
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ $lease->unit->property->name }} - {{ $lease->unit->label }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ $lease->primaryContact->name }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">
                                            ${{ number_format($lease->rent_amount_cents / 100, 2) }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $lease->start_date->format('M d') }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No recent leases</p>
                            @endforelse
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('leases.index') }}" class="text-sm text-blue-600 hover:text-blue-500">
                                View all leases →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Payments -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Payments</h3>
                        <div class="space-y-4">
                            @forelse($recentPayments as $payment)
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ $payment->contact->name }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ $payment->lease->unit->full_identifier }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-green-600">
                                            ${{ number_format($payment->amount_cents / 100, 2) }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $payment->posted_at->format('M d') }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No recent payments</p>
                            @endforelse
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('payments.index') }}" class="text-sm text-blue-600 hover:text-blue-500">
                                View all payments →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Maintenance -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Maintenance</h3>
                        <div class="space-y-4">
                            @forelse($recentTickets as $ticket)
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ $ticket->title }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ $ticket->property->name }}
                                            @if($ticket->unit)
                                                - {{ $ticket->unit->label }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="ml-2">
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                            @if($ticket->priority === 'emergency') bg-red-100 text-red-800
                                            @elseif($ticket->priority === 'high') bg-orange-100 text-orange-800
                                            @elseif($ticket->priority === 'medium') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($ticket->priority) }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No recent maintenance requests</p>
                            @endforelse
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('maintenance-tickets.index') }}" class="text-sm text-blue-600 hover:text-blue-500">
                                View all requests →
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Lease Expirations -->
            @if($upcomingExpirations->count() > 0)
                <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Upcoming Lease Expirations</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Unit
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tenant
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Expires
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($upcomingExpirations as $lease)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $lease->unit->full_identifier }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $lease->primaryContact->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $lease->end_date->format('M d, Y') }}
                                                <span class="text-xs text-gray-400">({{ $lease->end_date->diffForHumans() }})</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('leases.show', $lease) }}" class="text-blue-600 hover:text-blue-900">
                                                    View lease
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>