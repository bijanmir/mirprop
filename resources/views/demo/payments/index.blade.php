@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold">Payments</h1>
    <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to Dashboard</a>
  </div>

  <div class="bg-white shadow-sm rounded-xl overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">When</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payer</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Property / Unit</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-100">
        @foreach($payments as $p)
          <tr class="hover:bg-gray-50">
            <td class="px-6 py-4">{{ optional($p->posted_at)->toDayDateTimeString() ?? '—' }}</td>
            <td class="px-6 py-4">{{ optional($p->contact)->name ?? '—' }}</td>
            <td class="px-6 py-4">
              {{ optional($p->lease->unit->property)->name }} — {{ optional($p->lease->unit)->label }}
            </td>
            <td class="px-6 py-4">${{ number_format($p->amount_cents/100, 2) }}</td>
            <td class="px-6 py-4 uppercase">{{ $p->method }}</td>
            <td class="px-6 py-4 capitalize">{{ $p->status }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="px-6 py-4 border-t">{{ $payments->links() }}</div>
  </div>
</div>
@endsection
