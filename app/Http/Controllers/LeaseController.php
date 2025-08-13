<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaseRequest;
use App\Http\Requests\UpdateLeaseRequest;
use App\Models\Lease;
use App\Models\Unit;
use App\Models\Contact;
use App\Models\Property;
use App\Models\LeaseCharge;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaseController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Lease::class, 'lease');
    }

    public function index(Request $request)
    {
        $query = Lease::with(['unit.property', 'primaryContact'])
            ->withCount(['charges', 'payments']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('primaryContact', function ($sq) use ($search) {
                    $sq->where('name', 'ilike', "%{$search}%")
                      ->orWhere('email', 'ilike', "%{$search}%");
                })
                ->orWhereHas('unit.property', function ($sq) use ($search) {
                    $sq->where('name', 'ilike', "%{$search}%");
                })
                ->orWhereHas('unit', function ($sq) use ($search) {
                    $sq->where('label', 'ilike', "%{$search}%");
                });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by property
        if ($request->filled('property_id')) {
            $query->whereHas('unit', function ($q) use ($request) {
                $q->where('property_id', $request->property_id);
            });
        }

        // Filter by expiration
        if ($request->filled('expiration')) {
            switch ($request->expiration) {
                case 'expiring_soon':
                    $query->where('end_date', '<=', now()->addMonths(3))
                          ->where('status', 'active');
                    break;
                case 'expired':
                    $query->where('end_date', '<', now())
                          ->where('status', 'active');
                    break;
            }
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        $allowedSorts = ['created_at', 'start_date', 'end_date', 'rent_amount_cents'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $leases = $query->paginate(15)->withQueryString();

        // Get filter options
        $properties = Property::where('organization_id', auth()->user()->current_organization_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Return HTMX partial or full page
        if ($request->header('HX-Request')) {
            return view('leases.partials.table', compact('leases'));
        }

        return view('leases.index', compact('leases', 'properties'));
    }

    public function create(Request $request)
    {
        // Get available units (not currently leased)
        $availableUnits = Unit::where('organization_id', auth()->user()->current_organization_id)
            ->where('status', 'available')
            ->orWhereDoesntHave('leases', function ($query) {
                $query->where('status', 'active');
            })
            ->with('property')
            ->orderBy('property_id')
            ->get()
            ->groupBy('property.name');

        // Get tenant contacts
        $tenants = Contact::where('organization_id', auth()->user()->current_organization_id)
            ->where('type', 'tenant')
            ->orderBy('name')
            ->get();

        // Pre-select unit if provided
        $selectedUnitId = $request->get('unit_id');
        $selectedContactId = $request->get('contact_id');

        return view('leases.create', compact('availableUnits', 'tenants', 'selectedUnitId', 'selectedContactId'));
    }

    public function store(StoreLeaseRequest $request)
    {
        $validated = $request->validated();
        
        // Create the lease
        $lease = Lease::create($validated);

        // Update unit status to occupied
        $lease->unit->update(['status' => 'occupied']);

        // Create initial rent charge if specified
        if ($validated['create_rent_charge'] ?? false) {
            LeaseCharge::create([
                'lease_id' => $lease->id,
                'type' => 'rent',
                'amount_cents' => $lease->rent_amount_cents,
                'description' => 'Monthly Rent',
                'due_date' => $lease->start_date,
                'balance_cents' => $lease->rent_amount_cents,
                'is_recurring' => true,
                'day_of_month' => $lease->start_date->day,
            ]);
        }

        // Create deposit charge if amount provided
        if ($lease->deposit_amount_cents > 0) {
            LeaseCharge::create([
                'lease_id' => $lease->id,
                'type' => 'deposit',
                'amount_cents' => $lease->deposit_amount_cents,
                'description' => 'Security Deposit',
                'due_date' => $lease->start_date,
                'balance_cents' => $lease->deposit_amount_cents,
                'is_recurring' => false,
            ]);
        }

        if ($request->header('HX-Request')) {
            return response()
                ->view('leases.partials.created', compact('lease'))
                ->header('HX-Trigger', json_encode([
                    'toast' => [
                        'message' => 'Lease created successfully',
                        'type' => 'success'
                    ],
                    'leasesRefresh' => true
                ]));
        }

        return redirect()
            ->route('leases.show', $lease)
            ->with('success', 'Lease created successfully');
    }

    public function show(Lease $lease)
    {
        $lease->load([
            'unit.property',
            'primaryContact',
            'charges' => function ($query) {
                $query->orderBy('due_date');
            },
            'payments' => function ($query) {
                $query->orderBy('posted_at', 'desc')->limit(10);
            },
            'documents'
        ]);

        // Calculate lease metrics
        $metrics = [
            'total_charges' => $lease->charges->sum('amount_cents'),
            'total_payments' => $lease->payments->where('status', 'succeeded')->sum('amount_cents'),
            'outstanding_balance' => $lease->charges->sum('balance_cents'),
            'days_remaining' => now()->diffInDays($lease->end_date, false),
            'payment_history_count' => $lease->payments->count(),
        ];

        $metrics['collection_rate'] = $metrics['total_charges'] > 0 
            ? round(($metrics['total_payments'] / $metrics['total_charges']) * 100, 1)
            : 0;

        // Get recent activity (charges and payments combined)
        $recentActivity = collect()
            ->merge($lease->charges->map(function ($charge) {
                return [
                    'type' => 'charge',
                    'date' => $charge->due_date,
                    'description' => $charge->description,
                    'amount' => $charge->amount_cents,
                    'data' => $charge
                ];
            }))
            ->merge($lease->payments->map(function ($payment) {
                return [
                    'type' => 'payment',
                    'date' => $payment->posted_at ?? $payment->created_at,
                    'description' => 'Payment - ' . strtoupper($payment->method),
                    'amount' => $payment->amount_cents,
                    'data' => $payment
                ];
            }))
            ->sortByDesc('date')
            ->take(20);

        return view('leases.show', compact('lease', 'metrics', 'recentActivity'));
    }

    public function edit(Lease $lease)
    {
        // Get tenant contacts
        $tenants = Contact::where('organization_id', auth()->user()->current_organization_id)
            ->where('type', 'tenant')
            ->orderBy('name')
            ->get();

        if (request()->header('HX-Request')) {
            return view('leases.partials.edit-form', compact('lease', 'tenants'));
        }

        return view('leases.edit', compact('lease', 'tenants'));
    }

    public function update(UpdateLeaseRequest $request, Lease $lease)
    {
        $oldStatus = $lease->status;
        $lease->update($request->validated());

        // Handle status changes
        if ($oldStatus !== $lease->status) {
            switch ($lease->status) {
                case 'active':
                    $lease->unit->update(['status' => 'occupied']);
                    break;
                case 'terminated':
                case 'expired':
                    $lease->unit->update(['status' => 'available']);
                    break;
            }
        }

        if ($request->header('HX-Request')) {
            return response()
                ->view('leases.partials.updated', compact('lease'))
                ->header('HX-Trigger', json_encode([
                    'toast' => [
                        'message' => 'Lease updated successfully',
                        'type' => 'success'
                    ]
                ]));
        }

        return redirect()
            ->route('leases.show', $lease)
            ->with('success', 'Lease updated successfully');
    }

    public function destroy(Lease $lease)
    {
        // Check if lease has payments
        if ($lease->payments()->exists()) {
            if (request()->header('HX-Request')) {
                return response()
                    ->json(['error' => 'Cannot delete lease with existing payments'])
                    ->setStatusCode(422);
            }

            return redirect()
                ->route('leases.index')
                ->with('error', 'Cannot delete lease with existing payments. Please contact support if you need to remove this lease.');
        }

        // Update unit status to available if lease was active
        if ($lease->status === 'active') {
            $lease->unit->update(['status' => 'available']);
        }

        $lease->delete();

        if (request()->header('HX-Request')) {
            return response('')
                ->header('HX-Trigger', json_encode([
                    'toast' => [
                        'message' => 'Lease deleted successfully',
                        'type' => 'success'
                    ]
                ]));
        }

        return redirect()
            ->route('leases.index')
            ->with('success', 'Lease deleted successfully');
    }

    public function renew(Lease $lease)
    {
        // Create a new lease based on the current one
        $newLease = $lease->replicate();
        $newLease->start_date = $lease->end_date->addDay();
        $newLease->end_date = $newLease->start_date->copy()->addYear();
        $newLease->status = 'pending';
        
        return view('leases.renew', compact('lease', 'newLease'));
    }
}