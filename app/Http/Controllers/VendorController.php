<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVendorRequest;
use App\Http\Requests\UpdateVendorRequest;
use App\Models\Vendor;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VendorController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Vendor::class, 'vendor');
    }

    public function index(Request $request)
    {
        $query = Vendor::with(['contact'])
            ->withCount(['assignedTickets' => function ($query) {
                $query->whereIn('status', ['open', 'in_progress']);
            }]);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('contact', function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by service
        if ($request->filled('service')) {
            $service = $request->service;
            $query->whereJsonContains('services', $service);
        }

        $vendors = $query->paginate(15)->withQueryString();

        // Get all unique services for filter dropdown
        $allServices = Vendor::where('organization_id', auth()->user()->current_organization_id)
            ->whereNotNull('services')
            ->pluck('services')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        if ($request->header('HX-Request')) {
            return view('vendors.partials.table', compact('vendors', 'allServices'));
        }

        return view('vendors.index', compact('vendors', 'allServices'));
    }

    public function create()
    {
        // Get vendor contacts (contacts with type 'vendor' that don't have a vendor record)
        $vendorContacts = Contact::where('organization_id', auth()->user()->current_organization_id)
            ->where('type', 'vendor')
            ->whereDoesntHave('vendor')
            ->orderBy('name')
            ->get();

        return view('vendors.create', compact('vendorContacts'));
    }

    public function store(StoreVendorRequest $request)
    {
        $vendor = Vendor::create($request->validated());

        if ($request->header('HX-Request')) {
            return response()
                ->view('vendors.partials.created', compact('vendor'))
                ->header('HX-Trigger', json_encode([
                    'toast' => [
                        'message' => 'Vendor profile created successfully',
                        'type' => 'success'
                    ]
                ]));
        }

        return redirect()
            ->route('vendors.show', $vendor)
            ->with('success', 'Vendor profile created successfully');
    }

    public function show(Vendor $vendor)
    {
        $vendor->load([
            'contact',
            'assignedTickets' => function ($query) {
                $query->with(['property', 'unit'])->latest();
            }
        ]);

        // Calculate metrics
        $metrics = [
            'total_tickets' => $vendor->assignedTickets->count(),
            'open_tickets' => $vendor->assignedTickets->whereIn('status', ['open', 'in_progress'])->count(),
            'completed_tickets' => $vendor->assignedTickets->where('status', 'completed')->count(),
            'avg_completion_time' => 0, // TODO: Calculate from ticket events
        ];

        return view('vendors.show', compact('vendor', 'metrics'));
    }

    public function edit(Vendor $vendor)
    {
        if (request()->header('HX-Request')) {
            return view('vendors.partials.edit-form', compact('vendor'));
        }

        return view('vendors.edit', compact('vendor'));
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor)
    {
        $vendor->update($request->validated());

        if ($request->header('HX-Request')) {
            return response()
                ->view('vendors.partials.updated', compact('vendor'))
                ->header('HX-Trigger', json_encode([
                    'toast' => [
                        'message' => 'Vendor updated successfully',
                        'type' => 'success'
                    ]
                ]));
        }

        return redirect()
            ->route('vendors.show', $vendor)
            ->with('success', 'Vendor updated successfully');
    }

    public function destroy(Vendor $vendor)
    {
        // Check if vendor has assigned tickets
        if ($vendor->assignedTickets()->whereIn('status', ['open', 'in_progress'])->exists()) {
            if (request()->header('HX-Request')) {
                return response()
                    ->json(['error' => 'Cannot delete vendor with assigned maintenance tickets'])
                    ->setStatusCode(422);
            }

            return redirect()
                ->route('vendors.index')
                ->with('error', 'Cannot delete vendor with assigned maintenance tickets. Please reassign or complete tickets first.');
        }

        $vendor->delete();

        if (request()->header('HX-Request')) {
            return response('')
                ->header('HX-Trigger', json_encode([
                    'toast' => [
                        'message' => 'Vendor deleted successfully',
                        'type' => 'success'
                    ]
                ]));
        }

        return redirect()
            ->route('vendors.index')
            ->with('success', 'Vendor deleted successfully');
    }
}