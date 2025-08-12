<div id="properties-table">
  <div class="bg-white shadow-sm rounded-xl overflow-hidden">
    <div class="p-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <input type="text" 
                 name="search" 
                 placeholder="Search properties..."
                 value="{{ request('search') }}"
                 hx-get="{{ route('properties.index') }}"
                 hx-target="#properties-table"
                 hx-trigger="keyup changed delay:300ms"
                 hx-include="[name='sort'], [name='direction']"
                 class="block w-64 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
          
          <select name="type" 
                  hx-get="{{ route('properties.index') }}"
                  hx-target="#properties-table"
                  hx-trigger="change"
                  hx-include="[name='search'], [name='sort'], [name='direction']"
                  class="block px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            <option value="">All Types</option>
            <option value="residential" {{ request('type') === 'residential' ? 'selected' : '' }}>Residential</option>
            <option value="commercial" {{ request('type') === 'commercial' ? 'selected' : '' }}>Commercial</option>
            <option value="mixed" {{ request('type') === 'mixed' ? 'selected' : '' }}>Mixed Use</option>
          </select>
        </div>
        
        <div class="text-sm text-gray-500">
          {{ $properties->total() }} properties
        </div>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left">
              <button hx-get="{{ route('properties.index') }}"
                      hx-target="#properties-table"
                      hx-include="[name='search'], [name='type']"
                      hx-vals='{"sort": "name", "direction": "{{ request('sort') === 'name' && request('direction') === 'asc' ? 'desc' : 'asc' }}"}'
                      class="group inline-flex items-center text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                Property
                <span class="ml-2 flex-none rounded text-gray-400 group-hover:text-gray-700">
                  @if(request('sort') === 'name')
                    @if(request('direction') === 'asc')
                      <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                      </svg>
                    @else
                      <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"/>
                      </svg>
                    @endif
                  @else
                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                    </svg>
                  @endif
                </span>
              </button>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Units</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Occupancy</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
          @forelse($properties as $property)
            <tr class="hover:bg-gray-50" id="property-{{ $property->id }}">
              <td class="px-6 py-4">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ $property->name }}</div>
                  <div class="text-sm text-gray-500">
                    {{ $property->address_line1 }}<br>
                    {{ $property->city }}, {{ $property->state }} {{ $property->zip }}
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                  {{ $property->type === 'residential' ? 'bg-green-100 text-green-800' : '' }}
                  {{ $property->type === 'commercial' ? 'bg-blue-100 text-blue-800' : '' }}
                  {{ $property->type === 'mixed' ? 'bg-purple-100 text-purple-800' : '' }}
                ">
                  {{ ucfirst($property->type) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ $property->units_count }} units
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ $property->occupied_units_count }}/{{ $property->units_count }}
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                  <div class="bg-green-600 h-2 rounded-full" 
                       style="width: {{ $property->units_count > 0 ? ($property->occupied_units_count / $property->units_count * 100) : 0 }}%"></div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${{ number_format($property->monthly_revenue / 100, 0) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center space-x-2">
                  <a href="{{ route('properties.show', $property) }}" 
                     class="text-blue-600 hover:text-blue-900">View</a>
                  <a href="{{ route('properties.edit', $property) }}" 
                     class="text-gray-600 hover:text-gray-900">Edit</a>
                  <button hx-delete="{{ route('properties.destroy', $property) }}"
                          hx-confirm="Are you sure you want to delete this property?"
                          hx-target="#property-{{ $property->id }}"
                          hx-swap="outerHTML"
                          class="text-red-600 hover:text-red-900">
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-6 py-12 text-center">
                <div class="text-gray-400 mb-4">
                  <svg class="mx-auto h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                  </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No properties found</h3>
                <p class="text-gray-500 mb-6">
                  @if(request()->hasAny(['search', 'type']))
                    Try adjusting your filters or search terms.
                  @else
                    Get started by adding your first property.
                  @endif
                </p>
                <a href="{{ route('properties.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                  Add Property
                </a>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    
    @if($properties->hasPages())
      <div class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ $properties->firstItem() }} to {{ $properties->lastItem() }} of {{ $properties->total() }} results
          </div>
          <div class="flex space-x-1">
            @if($properties->onFirstPage())
              <span class="px-3 py-2 text-sm text-gray-400 cursor-not-allowed">Previous</span>
            @else
              <button hx-get="{{ $properties->previousPageUrl() }}"
                      hx-target="#properties-table"
                      hx-include="[name='search'], [name='type'], [name='sort'], [name='direction']"
                      class="px-3 py-2 text-sm text-blue-600 hover:text-blue-800">
                Previous
              </button>
            @endif
            
            @foreach($properties->getUrlRange(1, $properties->lastPage()) as $page => $url)
              @if($page == $properties->currentPage())
                <span class="px-3 py-2 text-sm bg-blue-50 text-blue-600 border border-blue-200 rounded">{{ $page }}</span>
              @else
                <button hx-get="{{ $url }}"
                        hx-target="#properties-table"
                        hx-include="[name='search'], [name='type'], [name='sort'], [name='direction']"
                        class="px-3 py-2 text-sm text-gray-700 hover:text-blue-600 border border-gray-300 rounded hover:bg-gray-50">
                  {{ $page }}
                </button>
              @endif
            @endforeach
            
            @if($properties->hasMorePages())
              <button hx-get="{{ $properties->nextPageUrl() }}"
                      hx-target="#properties-table"
                      hx-include="[name='search'], [name='type'], [name='sort'], [name='direction']"
                      class="px-3 py-2 text-sm text-blue-600 hover:text-blue-800">
                Next
              </button>
            @else
              <span class="px-3 py-2 text-sm text-gray-400 cursor-not-allowed">Next</span>
            @endif
          </div>
        </div>
      </div>
    @endif
  </div>
  
  <input type="hidden" name="sort" value="{{ request('sort') }}">
  <input type="hidden" name="direction" value="{{ request('direction') }}">
</div>