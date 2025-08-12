@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">
            Create Your Organization
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Set up your property management organization to get started
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <form method="POST" action="{{ route('orgs.store') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        Organization Name
                    </label>
                    <div class="mt-1">
                        <input id="name" 
                               name="name" 
                               type="text" 
                               required 
                               value="{{ old('name') }}"
                               placeholder="e.g., Sunset Property Management"
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('name') border-red-300 @enderror">
                    </div>
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700">
                        URL Slug
                    </label>
                    <div class="mt-1">
                        <div class="flex rounded-md shadow-sm">
                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                {{ config('app.url') }}/org/
                            </span>
                            <input id="slug" 
                                   name="slug" 
                                   type="text" 
                                   required 
                                   value="{{ old('slug') }}"
                                   placeholder="sunset-pm"
                                   pattern="[a-z0-9-]+"
                                   class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border border-gray-300 focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('slug') border-red-300 @enderror">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Lowercase letters, numbers, and hyphens only</p>
                    </div>
                    @error('slug')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="settings[currency]" class="block text-sm font-medium text-gray-700">
                        Currency
                    </label>
                    <div class="mt-1">
                        <select id="settings[currency]" 
                                name="settings[currency]" 
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="USD" {{ old('settings.currency', 'USD') === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                            <option value="CAD" {{ old('settings.currency') === 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                            <option value="EUR" {{ old('settings.currency') === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                            <option value="GBP" {{ old('settings.currency') === 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                        </select>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                        Create Organization
                    </button>
                </div>
            </form>

            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">Need help?</span>
                    </div>
                </div>
                <div class="mt-3 text-center">
                    <a href="mailto:support@mirprop.com" class="text-sm text-blue-600 hover:text-blue-500">
                        Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    
    nameInput.addEventListener('input', function() {
        if (!slugInput.dataset.userModified) {
            const slug = this.value
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
            slugInput.value = slug;
        }
    });
    
    slugInput.addEventListener('input', function() {
        this.dataset.userModified = 'true';
    });
});
</script>
@endsection