{{-- resources/views/leases/partials/updated.blade.php --}}
<tr class="hover:bg-gray-50 bg-green-50 transition-colors duration-1000" id="lease-row-{{ $lease->id }}">
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

<script>
    // Remove highlight after animation
    setTimeout(() => {
        document.getElementById('lease-row-{{ $lease->id }}')?.classList.remove('bg-green-50');
    }, 2000);
</script>