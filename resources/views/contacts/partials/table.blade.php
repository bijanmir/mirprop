<div id="contacts-table">
  <div class="bg-white shadow-sm rounded-xl overflow-hidden">
    <div class="p-4 border-b border-gray-200">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
          <input type="text" 
                 name="search" 
                 placeholder="Search contacts..."
                 value="{{ request('search') }}"
                 hx-get="{{ route('contacts.index') }}"
                 hx-target="#contacts-table"
                 hx-trigger="keyup changed delay:300ms"
                 hx-include="[name='type'], [name='status'], [name='sort'], [name='direction']"
                 class="block w-full sm:w-64 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
          
          <select name="type" 
                  hx-get="{{ route('contacts.index') }}"
                  hx-target="#contacts-table"
                  hx-trigger="change"
                  hx-include="[name='search'], [name='status'], [name='sort'], [name='direction']"
                  class="block px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            <option value="">All Types</option>
            <option value="tenant" {{ request('type') === 'tenant' ? 'selected' : '' }}>Tenants</option>
            <option value="vendor" {{ request('type') === 'vendor' ? 'selected' : '' }}>Vendors</option>
            <option value="owner" {{ request('type') === 'owner' ? 'selected' : '' }}>Owners</option>
            <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Other</option>
          </select>

          <select name="status" 
                  hx-get="{{ route('contacts.index') }}"
                  hx-target="#contacts-table"
                  hx-trigger="change"
                  hx-include="[name='search'], [name='type'], [name='sort'], [name='direction']"
                  class="block px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            <option value="">All Status</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Lease</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>No Active Lease</option>
          </select>
        </div>
        
        <div class="text-sm text-gray-500">
          {{ $contacts->total() }} contacts
        </div>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left">
              <button hx-get="{{ route('contacts.index') }}"
                      hx-target="#contacts-table"
                      hx-include="[name='search'], [name='type'], [name='status']"
                      hx-vals='{"sort": "name", "direction": "{{ request('sort') === 'name' && request('direction') === 'asc' ? 'desc' : 'asc' }}"}'
                      class="group inline-flex items-center text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                Contact
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
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Info</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Property</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
          @forelse($contacts as $contact)
            <tr class="hover:bg-gray-50" id="contact-{{ $contact->id }}">
              <td class="px-6 py-4">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full flex items-center justify-center text-white font-medium text-sm
                      {{ $contact->type === 'tenant' ? 'bg-green-500' : '' }}
                      {{ $contact->type === 'vendor' ? 'bg-blue-500' : '' }}
                      {{ $contact->type === 'owner' ? 'bg-purple-500' : '' }}
                      {{ $contact->type === 'other' ? 'bg-gray-500' : '' }}
                    ">
                      {{ strtoupper(substr($contact->name, 0, 2)) }}
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ $contact->name }}</div>
                    <div class="text-sm text-gray-500">Added {{ $contact->created_at->diffForHumans() }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                  {{ $contact->type === 'tenant' ? 'bg-green-100 text-green-800' : '' }}
                  {{ $contact->type === 'vendor' ? 'bg-blue-100 text-blue-800' : '' }}
                  {{ $contact->type === 'owner' ? 'bg-purple-100 text-purple-800' : '' }}
                  {{ $contact->type === 'other' ? 'bg-gray-100 text-gray-800' : '' }}
                ">
                  {{ ucfirst($contact->type) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  @if($contact->email)
                    <div>{{ $contact->email }}</div>
                  @endif
                  @if($contact->phone)
                    <div class="text-gray-500">{{ $contact->phone }}</div>
                  @endif
                  @if(!$contact->email && !$contact->phone)
                    <span class="text-gray-400">No contact info</span>
                  @endif
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                @if($contact->leases->where('status', 'active')->first())
                  @php $activeLease = $contact->leases->where('status', 'active')->first() @endphp
                  <div class="text-sm text-gray-900">{{ $activeLease->unit->property->name }}</div>
                  <div class="text-sm text-gray-500">Unit {{ $activeLease->unit->label }}</div>
                @else
                  <span class="text-sm text-gray-500">—</span>
                @endif
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center space-x-4 text-sm">
                  <div class="flex items-center text-gray-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    {{ $contact->leases_count }}
                  </div>
                  <div class="flex items-center text-gray-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    {{ $contact->payments_count }}
                  </div>
                  @if($contact->type === 'tenant')
                    <div class="flex items-center text-gray-500">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      </svg>
                      {{ $contact->maintenance_tickets_count }}
                    </div>
                  @endif
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center space-x-2">
                  <a href="{{ route('contacts.show', $contact) }}" 
                     class="text-blue-600 hover:text-blue-800">View</a>
                  <a href="{{ route('contacts.edit', $contact) }}" 
                     class="text-gray-600 hover:text-gray-800">Edit</a>
                  <button hx-delete="{{ route('contacts.destroy', $contact) }}"
                          hx-confirm="Are you sure you want to delete this contact?"
                          hx-target="#contact-{{ $contact->id }}"
                          hx-swap="outerHTML"
                          class="text-red-600 hover:text-red-800">
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No contacts found</h3>
                <p class="text-gray-500 mb-6">
                  @if(request()->hasAny(['search', 'type', 'status']))
                    Try adjusting your filters or search terms.
                  @else
                    Get started by adding your first contact.
                  @endif
                </p>
                <a href="{{ route('contacts.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                  Add Contact
                </a>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    
    @if($contacts->hasPages())
      <div class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ $contacts->firstItem() }} to {{ $contacts->lastItem() }} of {{ $contacts->total() }} results
          </div>
          <div class="flex space-x-1">
            @if($contacts->onFirstPage())
              <span class="px-3 py-2 text-sm text-gray-400 cursor-not-allowed">Previous</span>
            @else
              <button hx-get="{{ $contacts->previousPageUrl() }}"
                      hx-target="#contacts-table"
                      hx-include="[name='search'], [name='type'], [name='status'], [name='sort'], [name='direction']"
                      class="px-3 py-2 text-sm text-blue-600 hover:text-blue-800">
                Previous
              </button>
            @endif
            
            @foreach($contacts->getUrlRange(max(1, $contacts->currentPage() - 2), min($contacts->lastPage(), $contacts->currentPage() + 2)) as $page => $url)
              @if($page == $contacts->currentPage())
                <span class="px-3 py-2 text-sm bg-blue-50 text-blue-600 border border-blue-200 rounded">{{ $page }}</span>
              @else
                <button hx-get="{{ $url }}"
                        hx-target="#contacts-table"
                        hx-include="[name='search'], [name='type'], [name='status'], [name='sort'], [name='direction']"
                        class="px-3 py-2 text-sm text-gray-700 hover:text-blue-600 border border-gray-300 rounded hover:bg-gray-50">
                  {{ $page }}
                </button>
              @endif
            @endforeach
            
            @if($contacts->hasMorePages())
              <button hx-get="{{ $contacts->nextPageUrl() }}"
                      hx-target="#contacts-table"
                      hx-include="[name='search'], [name='type'], [name='status'], [name='sort'], [name='direction']"
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