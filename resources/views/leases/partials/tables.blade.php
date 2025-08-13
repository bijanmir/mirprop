{{-- resources/views/leases/partials/table.blade.php --}}
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <button 
                        hx-get="{{ route('leases.index', array_merge(request()->all(), ['sort' => 'unit', 'direction' => request('sort') === 'unit' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}"
                        hx-target="#lease-table-container"
                        hx-indicator="#sort-loading"
                        class="group flex items-center space-x-1 hover:text-gray-700">
                        <span>Property / Unit</span>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                        </svg>
                    </button>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <button 
                        hx-get="{{ route('leases.index', array_merge(request()->all(), ['sort' => 'tenant', 'direction' => request('sort') === 'tenant' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}"
                        hx-target="#lease-table-container"
                        class="group flex items-center space-x-1 hover:text-gray-700">
                        <span>Tenant</span>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                        </svg>
                    </button>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lease Period</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <button 
                        hx-get="{{ route('leases.index', array_merge(request()->all(), ['sort' => 'rent', 'direction' => request('sort') === 'rent' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}"
                        hx-target="#lease-table-container"
                        class="group flex items-center space-x-1 hover:text-gray-700">
                        <span>Monthly Rent</span>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                        </svg>
                    </button>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($leases as $lease)
                <tr class="hover:bg-gray-50" id="lease-row-{{ $lease->id }}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $lease->unit->property->name }}</div>
                        <div class="text-sm text-gray-500">{{ $lease->unit->label }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-{{ ['blue', 'purple', 'green', 'yellow', 'pink'][($lease->id % 5)] }}-100 flex items-center justify-center">
                                    <span class="text-sm font-medium text-{{ ['blue', 'purple', 'green', 'yellow', 'pink'][($lease->id % 5)] }}-800">
                                        {{ substr($lease->primaryContact->name, 0, 1) }}{{ substr(explode(' ', $lease->primaryContact->name)[1] ?? '', 0, 1) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $lease->primaryContact->name }}</div>
                                <div class="text-sm text-gray-500">{{ $lease->primaryContact->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">
                            {{ $lease->start_date->format('M j, Y') }} - {{ $lease->end_date->format('M j, Y') }}
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ $lease->start_date->diffInMonths($lease->end_date) }} months
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center space-x-2">
                            <span 
                                x-data="{ editing: false, value: {{ $lease->rent_amount_cents / 100 }} }"
                                x-show="!editing"
                                @click="editing = true"
                                class="text-sm font-medium text-gray-900 cursor-pointer hover:text-blue-600">
                                ${{ number_format($lease->rent_amount_cents / 100, 0) }}
                            </span>
                            <input 
                                x-data="{ editing: false, value: {{ $lease->rent_amount_cents / 100 }} }"
                                x-show="editing"
                                x-model="value"
                                type="number"
                                step="0.01"
                                min="0"
                                @blur="editing = false"
                                @keydown.enter="editing = false"
                                @keydown.escape="editing = false; value = {{ $lease->rent_amount_cents / 100 }}"
                                hx-patch="{{ route('leases.update', $lease) }}"
                                hx-trigger="blur, keydown[key=='Enter']"
                                hx-target="#lease-row-{{ $lease->id }}"
                                hx-include="[name='rent_amount']"
                                name="rent_amount"
                                class="w-24 px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                style="display: none;">
                            <button 
                                x-data="{ editing: false }"
                                @click="editing = !editing"
                                class="text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            @switch($lease->status)
                                @case('active')
                                    bg-green-100 text-green-800
                                    @break
                                @case('pending')
                                    bg-yellow-100 text-yellow-800
                                    @break
                                @case('expired')
                                    bg-red-100 text-red-800
                                    @break
                                @case('terminated')
                                    bg-gray-100 text-gray-800
                                    @break
                                @default
                                    bg-gray-100 text-gray-800
                            @endswitch">
                            {{ ucfirst($lease->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end space-x-2">
                            <a href="{{ route('leases.show', $lease) }}" 
                               class="text-blue-600 hover:text-blue-900">
                                View
                            </a>
                            <a href="{{ route('leases.edit', $lease) }}" 
                               class="text-gray-600 hover:text-gray-900">
                                Edit
                            </a>
                            @if($lease->status === 'active' && $lease->end_date->lt(now()->addMonths(3)))
                                <button 
                                    hx-get="{{ route('leases.renew', $lease) }}"
                                    hx-target="#main-content"
                                    class="text-green-600 hover:text-green-900">
                                    Renew
                                </button>
                            @endif
                            <button 
                                hx-delete="{{ route('leases.destroy', $lease) }}"
                                hx-confirm="Are you sure you want to terminate this lease? This cannot be undone."
                                hx-target="#lease-row-{{ $lease->id }}"
                                hx-swap="outerHTML swap:1s"
                                class="text-red-600 hover:text-red-900">
                                @if($lease->status === 'active')
                                    Terminate
                                @else
                                    Delete
                                @endif
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No leases found</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating a new lease.</p>
                            <div class="mt-6">
                                <button 
                                    @click="$dispatch('open-create-modal')"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Create Lease
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($leases->hasPages())
    <!-- Pagination -->
    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
        <div class="flex-1 flex justify-between sm:hidden">
            @if($leases->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-gray-100 cursor-not-allowed">
                    Previous
                </span>
            @else
                <button 
                    hx-get="{{ $leases->previousPageUrl() }}"
                    hx-target="#lease-table-container"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Previous
                </button>
            @endif
            
            @if($leases->hasMorePages())
                <button 
                    hx-get="{{ $leases->nextPageUrl() }}"
                    hx-target="#lease-table-container"
                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Next
                </button>
            @else
                <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-gray-100 cursor-not-allowed">
                    Next
                </span>
            @endif
        </div>
        
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing <span class="font-medium">{{ $leases->firstItem() ?? 0 }}</span> to 
                    <span class="font-medium">{{ $leases->lastItem() ?? 0 }}</span> of 
                    <span class="font-medium">{{ $leases->total() }}</span> leases
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    {{-- Previous Page Link --}}
                    @if($leases->onFirstPage())
                        <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-500 cursor-not-allowed">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </span>
                    @else
                        <button 
                            hx-get="{{ $leases->previousPageUrl() }}"
                            hx-target="#lease-table-container"
                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach($leases->getUrlRange(1, $leases->lastPage()) as $page => $url)
                        @if($page == $leases->currentPage())
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-blue-50 text-sm font-medium text-blue-600">
                                {{ $page }}
                            </span>
                        @else
                            <button 
                                hx-get="{{ $url }}"
                                hx-target="#lease-table-container"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if($leases->hasMorePages())
                        <button 
                            hx-get="{{ $leases->nextPageUrl() }}"
                            hx-target="#lease-table-container"
                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    @else
                        <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-500 cursor-not-allowed">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </span>
                    @endif
                </nav>
            </div>
        </div>
    </div>
@endif