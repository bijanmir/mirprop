@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
    <p class="text-gray-600 mt-1">Welcome back, {{ auth()->user()->name }}</p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Total Properties</p>
          <p class="text-2xl font-bold text-gray-900">{{ $stats['properties'] ?? 0 }}</p>
        </div>
        <div class="p-3 bg-blue-100 rounded-lg">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
          </svg>
        </div>
      </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Total Units</p>
          <p class="text-2xl font-bold text-gray-900">{{ $stats['units'] ?? 0 }}</p>
        </div>
        <div class="p-3 bg-green-100 rounded-lg">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2h14a2 2 0 012 2v2"></path>
          </svg>
        </div>
      </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Occupied Units</p>
          <p class="text-2xl font-bold text-gray-900">{{ $stats['occupied_units'] ?? 0 }}</p>
        </div>
        <div class="p-3 bg-purple-100 rounded-lg">
          <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
        </div>
      </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Monthly Revenue</p>
          <p class="text-2xl font-bold text-gray-900">${{ number_format(($stats['monthly_revenue'] ?? 0) / 100, 0) }}</p>
        </div>
        <div class="p-3 bg-yellow-100 rounded-lg">
          <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
          </svg>
        </div>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Recent Payments</h2>
        <a href="{{ route('demo.payments') }}" class="text-sm text-blue-600 hover:text-blue-800">View all</a>
      </div>
      <div class="space-y-3">
        @forelse($recentPayments ?? [] as $payment)
          <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
            <div>
              <p class="text-sm font-medium text-gray-900">{{ optional($payment->contact)->name ?? 'Unknown' }}</p>
              <p class="text-xs text-gray-500">{{ optional($payment->posted_at)->format('M j, Y') }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium text-gray-900">${{ number_format($payment->amount_cents / 100, 2) }}</p>
              <p class="text-xs text-gray-500 capitalize">{{ $payment->status }}</p>
            </div>
          </div>
        @empty
          <p class="text-sm text-gray-500 text-center py-4">No recent payments</p>
        @endforelse
      </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Open Maintenance</h2>
        <a href="{{ route('demo.tickets') }}" class="text-sm text-blue-600 hover:text-blue-800">View all</a>
      </div>
      <div class="space-y-3">
        @forelse($openTickets ?? [] as $ticket)
          <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
            <div>
              <p class="text-sm font-medium text-gray-900">{{ $ticket->title }}</p>
              <p class="text-xs text-gray-500">{{ optional($ticket->property)->name }} - {{ optional($ticket->unit)->label }}</p>
            </div>
            <div class="text-right">
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                {{ $ticket->priority === 'high' ? 'bg-red-100 text-red-800' : '' }}
                {{ $ticket->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : '' }}
                {{ $ticket->priority === 'low' ? 'bg-green-100 text-green-800' : '' }}
              ">
                {{ ucfirst($ticket->priority) }}
              </span>
            </div>
          </div>
        @empty
          <p class="text-sm text-gray-500 text-center py-4">No open tickets</p>
        @endforelse
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <a href="{{ route('demo.properties') }}" class="block bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition group">
      <div class="flex items-center">
        <div class="p-3 bg-blue-100 rounded-lg group-hover:bg-blue-200 transition">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
          </svg>
        </div>
        <div class="ml-4">
          <h3 class="text-lg font-medium text-gray-900">Properties</h3>
          <p class="text-sm text-gray-500">Manage your properties</p>
        </div>
      </div>
    </a>

    <a href="{{ route('demo.leases') }}" class="block bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition group">
      <div class="flex items-center">
        <div class="p-3 bg-green-100 rounded-lg group-hover:bg-green-200 transition">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </div>
        <div class="ml-4">
          <h3 class="text-lg font-medium text-gray-900">Leases</h3>
          <p class="text-sm text-gray-500">View active leases</p>
        </div>
      </div>
    </a>

    <a href="{{ route('demo.payments') }}" class="block bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition group">
      <div class="flex items-center">
        <div class="p-3 bg-yellow-100 rounded-lg group-hover:bg-yellow-200 transition">
          <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
          </svg>
        </div>
        <div class="ml-4">
          <h3 class="text-lg font-medium text-gray-900">Payments</h3>
          <p class="text-sm text-gray-500">Track rent payments</p>
        </div>
      </div>
    </a>

    <a href="{{ route('demo.tickets') }}" class="block bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition group">
      <div class="flex items-center">
        <div class="p-3 bg-purple-100 rounded-lg group-hover:bg-purple-200 transition">
          <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
        </div>
        <div class="ml-4">
          <h3 class="text-lg font-medium text-gray-900">Maintenance</h3>
          <p class="text-sm text-gray-500">Manage work orders</p>
        </div>
      </div>
    </a>

    <a href="#" class="block bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition group">
      <div class="flex items-center">
        <div class="p-3 bg-indigo-100 rounded-lg group-hover:bg-indigo-200 transition">
          <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
        </div>
        <div class="ml-4">
          <h3 class="text-lg font-medium text-gray-900">Tenants</h3>
          <p class="text-sm text-gray-500">Manage contacts</p>
        </div>
      </div>
    </a>

    <a href="#" class="block bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition group">
      <div class="flex items-center">
        <div class="p-3 bg-pink-100 rounded-lg group-hover:bg-pink-200 transition">
          <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
        </div>
        <div class="ml-4">
          <h3 class="text-lg font-medium text-gray-900">Reports</h3>
          <p class="text-sm text-gray-500">Financial reports</p>
        </div>
      </div>
    </a>
  </div>
</div>
@endsection