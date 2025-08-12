@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold">Leases</h1>
    <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to Dashboard</a>
  </div>

  <div class="bg-white shadow-sm rounded-xl overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tenant</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dates</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rent</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-100">
        @foreach($leases as $l)
          <tr class="hover:bg-gray-50">
            <td class="px-6 py-4">
              {{ $l->unit->property->name }} — {{ $l->unit->label }}
            </td>
            <td class="px-6 py-4">
              {{ optional($l->primaryContact)->name ?? '—' }}
            </td>
            <td class="px-6 py-4 text-gray-700">
              {{ \Illuminate\Support\Carbon::parse($l->start_date)->toFormattedDateString() }}
               – 
              {{ \Illuminate\Support\Carbon::parse($l->end_date)->toFormattedDateString() }}
            </td>
            <td class="px-6 py-4">${{ number_format($l->rent_amount_cents/100, 2) }}</td>
            <td class="px-6 py-4 capitalize">{{ $l->status }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="px-6 py-4 border-t">{{ $leases->links() }}</div>
  </div>
</div>
@endsection
