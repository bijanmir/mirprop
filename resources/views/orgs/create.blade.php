@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto py-10">
  <h1 class="text-2xl font-semibold mb-6">Create Organization</h1>
  <form method="POST" action="{{ route('orgs.store') }}" class="space-y-4">
    @csrf
    <div>
      <label class="block text-sm font-medium text-gray-700">Organization Name</label>
      <input name="name" required class="mt-1 w-full border rounded-lg px-3 py-2" />
      @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>
    <button class="px-4 py-2 rounded-lg bg-gray-900 text-white">Create</button>
  </form>
</div>
@endsection
