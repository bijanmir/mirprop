<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Unit;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UnitController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Unit::class, 'unit');
    }

    public function index(Request $request, Property $property = null)
    {
        $query = Unit::with(['property', 'activeLease.primaryContact']);

        // Filter by property if provided
        if ($property) {
            $this->authorize('view', $property);
            $query->where('property_id', $property->id);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('label', 'ilike', "%{$search}%")
                  ->orWhereHas('property', function ($pq) use ($search) {
                      $pq->where('name', 'ilike', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $units = $query->paginate(15)->withQueryString();

        if ($request->header('HX-Request')) {
            return view('units.partials.table', compact('units', 'property'));
        }

        return view('units.index', compact('units', 'property'));
    }

    public function create(Request $request, Property $property = null)
    {
        if ($property) {
            $this->authorize('manageUnits', $property);
        }

        $properties = Property::where('organization_id', auth()->user()->current_organization_id)
            ->orderBy('name')
            ->get();

        return view('units.create', compact('properties', 'property'));
    }

    public function store(StoreUnitRequest $request)
    {
        $unit = Unit::create($request->validated());

        if ($request->header('HX-Request')) {
            return response()
                ->view('units.partials.created', compact('unit'))
                ->header('HX-Trigger', json_encode([
                    'toast' => [
                        'message' => 'Unit created successfully',
                        'type' => 'success'
                    ]
                ]));
        }

        return redirect()
            ->route('units.show', $unit)
            ->with('success', 'Unit created successfully');
    }

    public function show(Unit $unit)
    {
        $unit->load([
            'property',
            'activeLease.primaryContact',
            'leases' => function ($query) {
                $query->with('primaryContact')->latest()->limit(5);
            },
            'maintenanceTickets' => function ($query) {
                $query->latest()->limit(5);
            }
        ]);

        return view('units.show', compact('unit'));
    }

    public function edit(Unit $unit)
    {
        $properties = Property::where('organization_id', auth()->user()->current_organization_id)
            ->orderBy('name')
            ->get();

        if (request()->header('HX-Request')) {
            return view('units.partials.edit-form', compact('unit', 'properties'));
        }

        return view('units.edit', compact('unit', 'properties'));
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $unit->update($request->validated());

        if ($request->header('HX-Request')) {
            return response()
                ->view('units.partials.updated', compact('unit'))
                ->header('HX-Trigger', json_encode([
                    'toast' => [
                        'message' => 'Unit updated successfully',
                        'type' => 'success'
                    ]
                ]));
        }

        return redirect()
            ->route('units.show', $unit)
            ->with('success', 'Unit updated successfully');
    }

    public function destroy(Unit $unit)
    {
        // Check if unit has active lease
        if ($unit->activeLease) {
            if (request()->header('HX-Request')) {
                return response()
                    ->json(['error' => 'Cannot delete unit with active lease'])
                    ->setStatusCode(422);
            }

            return redirect()
                ->route('units.index')
                ->with('error', 'Cannot delete unit with active lease. Please end the lease first.');
        }

        $unit->delete();

        if (request()->header('HX-Request')) {
            return response('')
                ->header('HX-Trigger', json_encode([
                    'toast' => [
                        'message' => 'Unit deleted successfully',
                        'type' => 'success'
                    ]
                ]));
        }

        return redirect()
            ->route('units.index')
            ->with('success', 'Unit deleted successfully');
    }
}