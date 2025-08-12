@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold">Maintenance Tickets</h1>
    <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to Dashboard</a>
  </div>

  <div class="bg-white shadow-sm rounded-xl overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Property / Unit</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-100">
        @foreach($tickets as $t)
          <tr class="hover:bg-gray-50">
            <td class="px-6 py-4">{{ $t->title }}</td>
            <td class="px-6 py-4">
              {{ optional($t->unit->property)->name ?? optional($t->property)->name }} — {{ optional($t->unit)->label ?? '—' }}
            </td>
            <td class="px-6 py-4 capitalize">{{ $t->priority }}</td>
            <td class="px-6 py-4 capitalize">{{ $t->status }}</td>
            <td class="px-6 py-4">{{ $t->created_at->toDayDateTimeString() }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="px-6 py-4 border-t">{{ $tickets->links() }}</div>
  </div>
</div>
@endsection
