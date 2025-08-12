@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold">Properties</h1>
    <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to Dashboard</a>
  </div>

  <div class="bg-white shadow-sm rounded-xl overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Property</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Units</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Occupancy</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-100">
        @foreach($properties as $p)
          @php
            $occupied = $occupiedCounts[$p->id] ?? 0;
            $rate = $p->units_count ? round(($occupied / $p->units_count) * 100) : 0;
          @endphp
          <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 font-medium text-gray-900">{{ $p->name }}</td>
            <td class="px-6 py-4 text-gray-700 capitalize">{{ $p->type }}</td>
            <td class="px-6 py-4 text-gray-700">{{ $p->units_count }}</td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-2">
                <span class="text-sm text-gray-900">{{ $rate }}%</span>
                <span class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                  <span class="block h-2 bg-green-600" style="width: {{ $rate }}%"></span>
                </span>
              </div>
            </td>
            <td class="px-6 py-4 text-gray-700">{{ $p->city }}, {{ $p->state }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="px-6 py-4 border-t">{{ $properties->links() }}</div>
  </div>
</div>
@endsection
