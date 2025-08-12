<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ContactController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Contact::class, 'contact');
    }

    public function index(Request $request)
    {
        $query = Contact::with(['leases' => function ($query) {
                $query->where('status', 'active')->with('unit.property');
            }])
            ->withCount(['leases', 'payments', 'maintenanceTickets']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status (has active lease or not)
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereHas('leases', function ($q) {
                    $q->where('status', 'active');
                });
            } elseif ($request->status === 'inactive') {
                $query->whereDoesntHave('leases', function ($q) {
                    $q->where('status', 'active');
                });
            }
        }

        // Sorting
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        
        $allowedSorts = ['name', 'email', 'type', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $contacts = $query->paginate(15)->withQueryString();

        // Return HTMX partial or full page
        if ($request->header('HX-Request')) {
            return view('contacts.partials.table', compact('contacts'));
        }

        return view('contacts.index', compact('contacts'));
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'tenant');
        return view('contacts.create', compact('type'));
    }

    public function store(StoreContactRequest $request)
    {
        $contact = Contact::create($request->validated());

        // Create vendor record if contact type is vendor
        if ($contact->type === 'vendor') {
            $contact->vendor()->create([
                'organization_id' => $contact->organization_id,
                'services' => [],
                'is_active' => true,
            ]);
        }

        if ($request->header('HX-Request')) {
            return response()
                ->view('contacts.partials.created', compact('contact'))
                ->header('HX-Trigger', json_encode([
                    'toast' => [
                        'message' => 'Contact created successfully',
                        'type' => 'success'
                    ],
                    'contactsRefresh' => true
                ]));
        }

        return redirect()
            ->route('contacts.show', $contact)
            ->with('success', 'Contact created successfully');
    }

    public function show(Contact $contact)
    {
        $contact->load([
            'leases' => function ($query) {
                $query->with(['unit.property'])->latest();
            },
            'payments' => function ($query) {
                $query->latest()->limit(10);
            },
            'maintenanceTickets' => function ($query) {
                $query->with(['property', 'unit'])->latest()->limit(10);
            },
            'vendor.assignedTickets' => function ($query) {
                $query->with(['property', 'unit'])->whereIn('status', ['open', 'in_progress'])->latest();
            }
        ]);

        // Calculate metrics
        $metrics = [
            'active_leases' => $contact->leases->where('status', 'active')->count(),
            'total_payments' => $contact->payments->sum('amount_cents'),
            'open_tickets' => $contact->maintenanceTickets->whereIn('status', ['open', 'in_progress'])->count(),
            'avg_response_time' => 0, // TODO: Calculate from maintenance events
        ];

        return view('contacts.show', compact('contact', 'metrics'));
    }

    public function edit(Contact $contact)
    {
        if (request()->header('HX-Request')) {
            return view('contacts.partials.edit-form', compact('contact'));
        }

        return view('contacts.edit', compact('contact'));
    }

    public function update(UpdateContactRequest $request, Contact $contact)
    {
        $contact->update($request->validated());

        if ($request->header('HX-Request')) {
            return response()
                ->view('contacts.partials.updated', compact('contact'))
                ->header('HX-Trigger', json_encode([
                    'toast' => [
                        'message' => 'Contact updated successfully',
                        'type' => 'success'
                    ]
                ]));
        }

        return redirect()
            ->route('contacts.show', $contact)
            ->with('success', 'Contact updated successfully');
    }

    public function destroy(Contact $contact)
    {
        // Check if contact has active leases
        if ($contact->leases()->where('status', 'active')->exists()) {
            if (request()->header('HX-Request')) {
                return response()
                    ->json(['error' => 'Cannot delete contact with active leases'])
                    ->setStatusCode(422);
            }

            return redirect()
                ->route('contacts.index')
                ->with('error', 'Cannot delete contact with active leases. Please end all leases first.');
        }

        // Check if vendor has assigned tickets
        if ($contact->type === 'vendor' && $contact->vendor && 
            $contact->vendor->assignedTickets()->whereIn('status', ['open', 'in_progress'])->exists()) {
            
            if (request()->header('HX-Request')) {
                return response()
                    ->json(['error' => 'Cannot delete vendor with assigned maintenance tickets'])
                    ->setStatusCode(422);
            }

            return redirect()
                ->route('contacts.index')
                ->with('error', 'Cannot delete vendor with assigned maintenance tickets. Please reassign or complete tickets first.');
        }

        $contact->delete();

        if (request()->header('HX-Request')) {
            return response('')
                ->header('HX-Trigger', json_encode([
                    'toast' => [
                        'message' => 'Contact deleted successfully',
                        'type' => 'success'
                    ]
                ]));
        }

        return redirect()
            ->route('contacts.index')
            ->with('success', 'Contact deleted successfully');
    }
}