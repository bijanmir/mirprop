<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVendorRequest;
use App\Http\Requests\UpdateVendorRequest;
use App\Models\Contact;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Vendor::class, 'vendor');
    }

    public function index(Request $request)
    {
        $vendors = Vendor::with('contact')
            ->when($request->search, function ($query, $search) {
                $query->whereHas('contact', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->service, function ($query, $service) {
                $query->whereJsonContains('services', $service);
            })
            ->when($request->has('active'), fn($q) => $q->where('is_active', $request->boolean('active')))
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        if ($request->header('HX-Request')) {
            return view('vendors.partials.table', compact('vendors'));
        }
        
        return view('vendors.index', compact('vendors'));
    }

    public function create()
    {
        $contacts = Contact::where('type', 'vendor')
            ->whereDoesntHave('vendor')
            ->get();
        
        if (request()->header('HX-Request')) {
            return view('vendors.partials.create-form', compact('contacts'));
        }
        
        return view('vendors.create', compact('contacts'));
    }

    public function store(StoreVendorRequest $request)
    {
        $vendor = Vendor::create($request->validated());
        
        if ($request->header('HX-Request')) {
            return response()
                ->view('vendors.partials.row', compact('vendor'))
                ->header('HX-Trigger', json_encode([
                    'close-modal' => true,
                    'toast' => [
                        'message' => 'Vendor created successfully',
                        'type' => 'success'
                    ]
                ]));
        }
        
        return redirect()
            ->route('vendors.show', $vendor)
            ->with('success', 'Vendor created successfully');
    }

    public function show(Vendor $vendor)
    {
        $vendor->load(['contact', 'assignedTickets' => function ($query) {
            $query->latest()->limit(10);
        }]);
        
        $stats = [
            'total_tickets' => $vendor->assignedTickets()->count(),
            'open_tickets' => $vendor->assignedTickets()->whereIn('status', ['assigned', 'in_progress'])->count(),
            'completed_tickets' => $vendor->assignedTickets()->where('status', 'completed')->count(),
        ];
        
        return view('vendors.show', compact('vendor', 'stats'));
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
                ->view('vendors.partials.row', compact('vendor'))
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