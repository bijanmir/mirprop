<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaseRequest;
use App\Http\Requests\UpdateLeaseRequest;
use App\Models\Contact;
use App\Models\Lease;
use App\Models\Unit;
use Illuminate\Http\Request;

class LeaseController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Lease::class, 'lease');
    }

    public function index(Request $request)
    {
        $leases = Lease::with(['unit.property', 'primaryContact'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('unit.property', fn($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('primaryContact', fn($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->property_id, function ($query, $propertyId) {
                $query->whereHas('unit', fn($q) => $q->where('property_id', $propertyId));
            })
            ->orderBy($request->get('sort', 'created_at'), $request->get('direction', 'desc'))
            ->paginate(15);
        
        if ($request->header('HX-Request')) {
            return view('leases.partials.table', compact('leases'));
        }
        
        $properties = auth()->user()->currentOrganization->properties()->get();
        
        return view('leases.index', compact('leases', 'properties'));
    }

    public function create()
    {
        $units = Unit::where('status', 'available')
            ->with('property')
            ->get();
        
        $contacts = Contact::whereIn('type', ['tenant', 'other'])->get();
        
        if (request()->header('HX-Request')) {
            return view('leases.partials.create-form', compact('units', 'contacts'));
        }
        
        return view('leases.create', compact('units', 'contacts'));
    }

    public function store(StoreLeaseRequest $request)
    {
        $lease = Lease::create($request->validated());
        
        // Create initial rent charge
        $lease->charges()->create([
            'type' => 'rent',
            'amount_cents' => $lease->rent_amount_cents,
            'description' => 'Monthly Rent',
            'due_date' => $lease->start_date->day($request->input('rent_due_day', 1)),
            'balance_cents' => $lease->rent_amount_cents,
            'is_recurring' => true,
            'day_of_month' => $request->input('rent_due_day', 1),
        ]);
        
        // Create deposit charge if applicable
        if ($lease->deposit_amount_cents > 0) {
            $lease->charges()->create([
                'type' => 'deposit',
                'amount_cents' => $lease->deposit_amount_cents,
                'description' => 'Security Deposit',
                'due_date' => $lease->start_date,
                'balance_cents' => $lease->deposit_amount_cents,
                'is_recurring' => false,
            ]);
        }
        
        // Update unit status
        $lease->unit->update(['status' => 'occupied']);
        
        if ($request->header('HX-Request')) {
            return response()
                ->view('leases.partials.create-success', compact('lease'))
                ->header('HX-Trigger', json_encode([
                    'close-modal' => true,
                    'refresh-table' => true,
                    'toast' => [
                        'message' => 'Lease created successfully',
                        'type' => 'success'
                    ]
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
            'charges',
            'payments',
            'documents'
        ]);
        
        $balance = $lease->charges()->sum('balance_cents');
        
        return view('leases.show', compact('lease', 'balance'));
    }

    public function edit(Lease $lease)
    {
        if (request()->header('HX-Request')) {
            return view('leases.partials.edit-form', compact('lease'));
        }
        
        return view('leases.edit', compact('lease'));
    }

    public function update(UpdateLeaseRequest $request, Lease $lease)
    {
        $lease->update($request->validated());
        
        // Update recurring rent charge if amount changed
        if ($request->has('rent_amount_cents')) {
            $lease->charges()
                ->where('type', 'rent')
                ->where('is_recurring', true)
                ->update(['amount_cents' => $request->rent_amount_cents]);
        }
        
        if ($request->header('HX-Request')) {
            return response()
                ->view('leases.partials.row', compact('lease'))
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
        // Update unit status if lease is active
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
    
    public function uploadDocument(Request $request, Lease $lease)
    {
        $this->authorize('update', $lease);
        
        $request->validate([
            'file' => ['required', 'file', 'max:20480'], // 20MB
            'tags' => ['nullable', 'array'],
        ]);
        
        $path = $request->file('file')->store("leases/{$lease->id}", 's3');
        
        $document = $lease->documents()->create([
            'organization_id' => auth()->user()->current_organization_id,
            'filename' => $request->file('file')->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $request->file('file')->getMimeType(),
            'size' => $request->file('file')->getSize(),
            'tags' => $request->tags ?? [],
        ]);
        
        if ($request->header('HX-Request')) {
            return view('documents.partials.row', compact('document'));
        }
        
        return redirect()
            ->route('leases.show', $lease)
            ->with('success', 'Document uploaded successfully');
    }
    
    public function generateAiSummary(Request $request, Lease $lease)
    {
        $this->authorize('update', $lease);
        
        // This would queue a job to process AI summary
        // For now, just return success
        
        return response()->json([
            'message' => 'AI summary generation started',
            'status' => 'processing'
        ]);
    }
}