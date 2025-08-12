<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignMaintenanceTicketRequest;
use App\Http\Requests\StoreMaintenanceTicketRequest;
use App\Http\Requests\UpdateMaintenanceTicketRequest;
use App\Models\MaintenanceTicket;
use App\Models\Property;
use App\Models\Vendor;
use Illuminate\Http\Request;

class MaintenanceTicketController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(MaintenanceTicket::class, 'maintenance_ticket');
    }

    public function index(Request $request)
    {
        $query = MaintenanceTicket::with(['property', 'unit', 'contact', 'assignedVendor.contact']);
        
        // Filter based on user role
        if (auth()->user()->hasOrganizationRole('tenant')) {
            $contact = auth()->user()->currentOrganization->contacts()
                ->where('email', auth()->user()->email)
                ->first();
            
            if ($contact) {
                $query->where('contact_id', $contact->id);
            }
        } elseif (auth()->user()->hasOrganizationRole('vendor')) {
            $vendor = auth()->user()->currentOrganization->vendors()
                ->whereHas('contact', fn($q) => $q->where('email', auth()->user()->email))
                ->first();
            
            if ($vendor) {
                $query->where('assigned_vendor_id', $vendor->id);
            }
        }
        
        $tickets = $query
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->priority, fn($q, $priority) => $q->where('priority', $priority))
            ->when($request->property_id, fn($q, $id) => $q->where('property_id', $id))
            ->orderBy($request->get('sort', 'created_at'), $request->get('direction', 'desc'))
            ->paginate(15);
        
        if ($request->header('HX-Request')) {
            return view('maintenance-tickets.partials.table', compact('tickets'));
        }
        
        $properties = auth()->user()->currentOrganization->properties()->get();
        
        return view('maintenance-tickets.index', compact('tickets', 'properties'));
    }

    public function create()
    {
        $properties = Property::with('units')->get();
        
        if (request()->header('HX-Request')) {
            return view('maintenance-tickets.partials.create-form', compact('properties'));
        }
        
        return view('maintenance-tickets.create', compact('properties'));
    }

    public function store(StoreMaintenanceTicketRequest $request)
    {
        $ticket = MaintenanceTicket::create($request->validated());
        
        // Create initial event
        $ticket->events()->create([
            'actor_id' => auth()->id(),
            'type' => 'note',
            'notes' => 'Ticket created',
        ]);
        
        // Handle image uploads
        if ($request->hasFile('images')) {
            $paths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store("tickets/{$ticket->id}", 's3');
                $paths[] = $path;
            }
            
            $ticket->events()->create([
                'actor_id' => auth()->id(),
                'type' => 'attachment',
                'attachments' => $paths,
                'notes' => count($paths) . ' image(s) uploaded',
            ]);
        }
        
        if ($request->header('HX-Request')) {
            return response()
                ->view('maintenance-tickets.partials.create-success', compact('ticket'))
                ->header('HX-Trigger', json_encode([
                    'close-modal' => true,
                    'refresh-table' => true,
                    'toast' => [
                        'message' => 'Maintenance request created successfully',
                        'type' => 'success'
                    ]
                ]));
        }
        
        return redirect()
            ->route('maintenance-tickets.show', $ticket)
            ->with('success', 'Maintenance request created successfully');
    }

    public function show(MaintenanceTicket $maintenanceTicket)
    {
        $maintenanceTicket->load([
            'property',
            'unit',
            'contact',
            'assignedVendor.contact',
            'events.actor',
            'documents'
        ]);
        
        return view('maintenance-tickets.show', ['ticket' => $maintenanceTicket]);
    }

    public function edit(MaintenanceTicket $maintenanceTicket)
    {
        if (request()->header('HX-Request')) {
            return view('maintenance-tickets.partials.edit-form', ['ticket' => $maintenanceTicket]);
        }
        
        return view('maintenance-tickets.edit', ['ticket' => $maintenanceTicket]);
    }

    public function update(UpdateMaintenanceTicketRequest $request, MaintenanceTicket $maintenanceTicket)
    {
        $changes = [];
        
        // Track status changes
        if ($request->has('status') && $request->status !== $maintenanceTicket->status) {
            $changes['status'] = [
                'old' => $maintenanceTicket->status,
                'new' => $request->status
            ];
            
            if ($request->status === 'completed') {
                $maintenanceTicket->completed_at = now();
            }
        }
        
        // Track priority changes
        if ($request->has('priority') && $request->priority !== $maintenanceTicket->priority) {
            $changes['priority'] = [
                'old' => $maintenanceTicket->priority,
                'new' => $request->priority
            ];
        }
        
        $maintenanceTicket->update($request->validated());
        
        // Create event for changes
        if (!empty($changes)) {
            $maintenanceTicket->events()->create([
                'actor_id' => auth()->id(),
                'type' => 'status_change',
                'meta' => $changes,
                'notes' => 'Updated ticket',
            ]);
        }
        
        if ($request->header('HX-Request')) {
            return response()
                ->view('maintenance-tickets.partials.row', ['ticket' => $maintenanceTicket])
                ->header('HX-Trigger', json_encode([
                    'toast' => [
                        'message' => 'Ticket updated successfully',
                        'type' => 'success'
                    ]
                ]));
        }
        
        return redirect()
            ->route('maintenance-tickets.show', $maintenanceTicket)
            ->with('success', 'Ticket updated successfully');
    }

    public function destroy(MaintenanceTicket $maintenanceTicket)
    {
        $maintenanceTicket->delete();
        
        if (request()->header('HX-Request')) {
            return response('')
                ->header('HX-Trigger', json_encode([
                    'toast' => [
                        'message' => 'Ticket deleted successfully',
                        'type' => 'success'
                    ]
                ]));
        }
        
        return redirect()
            ->route('maintenance-tickets.index')
            ->with('success', 'Ticket deleted successfully');
    }
    
    public function assign(AssignMaintenanceTicketRequest $request, MaintenanceTicket $ticket)
    {
        $this->authorize('update', $ticket);
        
        $vendor = Vendor::findOrFail($request->vendor_id);
        
        $ticket->update([
            'assigned_vendor_id' => $vendor->id,
            'status' => 'assigned',
        ]);
        
        $ticket->events()->create([
            'actor_id' => auth()->id(),
            'type' => 'assignment',
            'notes' => $request->notes ?? "Assigned to {$vendor->contact->name}",
            'meta' => [
                'vendor_id' => $vendor->id,
                'vendor_name' => $vendor->contact->name,
            ],
        ]);
        
        // TODO: Send notification to vendor
        
        if ($request->header('HX-Request')) {
            return response()
                ->view('maintenance-tickets.partials.assignment-success', compact('ticket', 'vendor'))
                ->header('HX-Trigger', json_encode([
                    'close-modal' => true,
                    'toast' => [
                        'message' => 'Vendor assigned successfully',
                        'type' => 'success'
                    ]
                ]));
        }
        
        return redirect()
            ->route('maintenance-tickets.show', $ticket)
            ->with('success', 'Vendor assigned successfully');
    }
    
    public function addEvent(Request $request, MaintenanceTicket $ticket)
    {
        $this->authorize('update', $ticket);
        
        $request->validate([
            'type' => ['required', 'in:note,cost'],
            'notes' => ['required_if:type,note', 'string', 'max:5000'],
            'cost' => ['required_if:type,cost', 'numeric', 'min:0'],
        ]);
        
        $data = [
            'actor_id' => auth()->id(),
            'type' => $request->type,
            'notes' => $request->notes,
        ];
        
        if ($request->type === 'cost') {
            $data['cost_cents'] = $request->cost * 100;
        }
        
        $event = $ticket->events()->create($data);
        
        if ($request->header('HX-Request')) {
            return view('maintenance-tickets.partials.event', compact('event'));
        }
        
        return redirect()
            ->route('maintenance-tickets.show', $ticket)
            ->with('success', 'Event added successfully');
    }
}