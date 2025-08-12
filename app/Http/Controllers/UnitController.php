<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Property;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Unit::class, 'unit');
    }

    public function index(Request $request, Property $property)
    {
        $this->authorize('view', $property);
        
        $units = $property->units()
            ->with(['activeLease.primaryContact'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->orderBy('label')
            ->get();
        
        if ($request->header('HX-Request')) {
            return view('units.partials.list', compact('units', 'property'));
        }
        
        return view('units.index', compact('units', 'property'));
    }

    public function create(Property $property)
    {
        $this->authorize('create', Unit::class);
        
        if (request()->header('HX-Request')) {
            return view('units.partials.create-form', compact('property'));
        }
        
        return view('units.create', compact('property'));
    }

    public function store(StoreUnitRequest $request, Property $property)
    {
        $unit = $property->units()->create($request->validated());
        
        if ($request->header('HX-Request')) {
            return response()
                ->view('units.partials.row', compact('unit'))
                ->header('HX-Trigger', json_encode([
                    'close-modal' => true,
                    'toast' => [
                        'message' => 'Unit created successfully',
                        'type' => 'success'
                    ]
                ]));
        }
        
        return redirect()
            ->route('properties.show', $property)
            ->with('success', 'Unit created successfully');
    }

    public function show(Unit $unit)
    {
        $unit->load(['property', 'leases.primaryContact', 'maintenanceTickets']);
        
        return view('units.show', compact('unit'));
    }

    public function edit(Unit $unit)
    {
        if (request()->header('HX-Request')) {
            return view('units.partials.edit-form', compact('unit'));
        }
        
        return view('units.edit', compact('unit'));
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $unit->update($request->validated());
        
        if ($request->header('HX-Request')) {
            return response()
                ->view('units.partials.row', compact('unit'))
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
            ->route('properties.show', $unit->property)
            ->with('success', 'Unit deleted successfully');
    }
}