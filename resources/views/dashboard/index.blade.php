{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 space-y-8">

  <div class="flex items-start justify-between">
    <div>
      <h1 class="text-2xl font-semibold">Dashboard</h1>
      <p class="text-sm text-gray-600">Portfolio overview for your organization.</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('demo.properties') }}" class="px-3 py-2 bg-gray-900 text-white rounded-lg text-sm">Properties</a>
      <a href="{{ route('demo.tickets') }}" class="px-3 py-2 bg-white border rounded-lg text-sm">Maintenance</a>
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl p-5 shadow-sm">
      <div class="text-sm text-gray-500">Total Units</div>
      <div class="text-2xl font-semibold mt-1">{{ number_format($metrics['total_units']) }}</div>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm">
      <div class="text-sm text-gray-500">Occupied</div>
      <div class="text-2xl font-semibold mt-1">{{ number_format($metrics['occupied_units']) }}</div>
      <div class="mt-2 text-xs text-gray-500">Vacant: {{ number_format($metrics['vacant_units']) }}</div>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm">
      <div class="text-sm text-gray-500">Occupancy</div>
      <div class="text-2xl font-semibold mt-1">{{ $metrics['occupancy_rate'] }}%</div>
      <div class="mt-2 w-full h-2 bg-gray-200 rounded-full overflow-hidden">
        <div class="h-2 bg-green-600" style="width: {{ $metrics['occupancy_rate'] }}%"></div>
      </div>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm">
      <div class="text-sm text-gray-500">Open Tickets</div>
      <div class="text-2xl font-semibold mt-1">{{ number_format($metrics['open_tickets']) }}</div>
      <div class="mt-2 text-xs text-red-600">Emergency: {{ number_format($metrics['emergency_tickets']) }}</div>
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl p-5 shadow-sm sm:col-span-1">
      <div class="text-sm text-gray-500">Rent Due (This Month)</div>
      <div class="text-2xl font-semibold mt-1">${{ number_format($metrics['rent_due']/100, 2) }}</div>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm sm:col-span-1">
      <div class="text-sm text-gray-500">Rent Collected</div>
      <div class="text-2xl font-semibold mt-1">${{ number_format($metrics['rent_collected']/100, 2) }}</div>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm sm:col-span-1">
      <div class="text-sm text-gray-500">Collection Rate</div>
      <div class="text-2xl font-semibold mt-1">{{ $metrics['collection_rate'] }}%</div>
      <div class="mt-2 w-full h-2 bg-gray-200 rounded-full overflow-hidden">
        <div class="h-2 bg-green-600" style="width: {{ $metrics['collection_rate'] }}%"></div>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl p-5 shadow-sm lg:col-span-1">
      <h2 class="text-lg font-semibold mb-3">Recent Tickets</h2>
      <div class="divide-y">
        @forelse($recentTickets as $t)
          <div class="py-3">
            <div class="text-sm font-medium text-gray-900">{{ $t->title }}</div>
            <div class="text-xs text-gray-600">
              {{ optional($t->property)->name }} — {{ optional($t->unit)->label ?? '—' }}
              • <span class="capitalize">{{ $t->priority }}</span>
              • <span class="capitalize">{{ $t->status }}</span>
            </div>
          </div>
        @empty
          <div class="py-6 text-sm text-gray-500">No tickets yet.</div>
        @endforelse
      </div>
    </div>

    <div class="bg-white rounded-xl p-5 shadow-sm lg:col-span-2">
      <h2 class="text-lg font-semibold mb-3">Recent Payments</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">When</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Payer</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            @forelse($recentPayments as $p)
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 text-sm text-gray-700">{{ optional($p->posted_at ?? $p->created_at)->diffForHumans() }}</td>
                <td class="px-4 py-2 text-sm">{{ optional($p->contact)->name ?? '—' }}</td>
                <td class="px-4 py-2 text-sm">
                  {{ optional($p->lease->unit->property)->name }} — {{ optional($p->lease->unit)->label }}
                </td>
                <td class="px-4 py-2 text-sm">${{ number_format($p->amount_cents/100, 2) }}</td>
                <td class="px-4 py-2 text-sm uppercase">{{ $p->method }}</td>
              </tr>
            @empty
              <tr><td colspan="5" class="px-4 py-6 text-sm text-gray-500">No payments yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-xl p-5 shadow-sm">
    <h2 class="text-lg font-semibold mb-3">Leases Expiring Soon</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Property / Unit</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tenant</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ends</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
          @forelse($upcomingExpirations as $l)
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-2 text-sm">
                {{ optional($l->unit->property)->name }} — {{ optional($l->unit)->label }}
              </td>
              <td class="px-4 py-2 text-sm">{{ optional($l->primaryContact)->name ?? '—' }}</td>
              <td class="px-4 py-2 text-sm">
                {{ \Illuminate\Support\Carbon::parse($l->end_date)->toFormattedDateString() }}
              </td>
            </tr>
          @empty
            <tr><td colspan="3" class="px-4 py-6 text-sm text-gray-500">Nothing expiring soon.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection
