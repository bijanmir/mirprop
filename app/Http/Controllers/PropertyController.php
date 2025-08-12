<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Property::class);
        
        $properties = Property::withCount(['units', 'units as occupied_units_count' => function ($query) {
                $query->where('status', 'occupied');
            }])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('address_line1', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->orderBy($request->get('sort', 'name'), $request->get('direction', 'asc'))
            ->paginate(15);
        
        if ($request->header('HX-Request')) {
            return view('properties.partials.table', compact('properties'));
        }
        
        return view('properties.index', compact('properties'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Property::class);
        
        if (request()->header('HX-Request')) {
            return view('properties.partials.create-form');
        }
        
        return view('properties.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePropertyRequest $request)
    {
        $property = Property::create($request->validated());
        
        if ($request->header('HX-Request')) {
            return response()
                ->view('properties.partials.row', compact('property'))
                ->header('HX-Trigger', json_encode([
                    'close-modal' => true,
                    'refresh-table' => true,
                    'toast' => [
                        'message' => 'Property created successfully',
                        'type' => 'success'
                    ]
                ]));
        }
        
        return redirect()
            ->route('properties.show', $property)
            ->with('success', 'Property created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Property $property)
    {
        $this->authorize('view', $property);
        
        $property->load(['units' => function ($query) {
            $query->with('activeLease.primaryContact')
                ->orderBy('label');
        }]);
        
        $metrics = [
            'total_units' => $property->units->count(),
            'occupied_units' => $property->units->where('status', 'occupied')->count(),
            'vacant_units' => $property->units->where('status', 'vacant')->count(),
            'monthly_rent' => $property->units->sum('rent_amount_cents'),
        ];
        
        $metrics['occupancy_rate'] = $metrics['total_units'] > 0 
            ? round(($metrics['occupied_units'] / $metrics['total_units']) * 100, 1)
            : 0;
        
        return view('properties.show', compact('property', 'metrics'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Property $property)
    {
        $this->authorize('update', $property);
        
        if (request()->header('HX-Request')) {
            return view('properties.partials.edit-form', compact('property'));
        }
        
        return view('properties.edit', compact('property'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePropertyRequest $request, Property $property)
    {
        $this->authorize('update', $property);
        
        $property->update($request->validated());
        
        if ($request->header('HX-Request')) {
            return response()
                ->view('properties.partials.row', compact('property'))
                ->header('HX-Trigger', json_encode([
                    'toast' => [
                        'message' => 'Property updated successfully',
                        'type' => 'success'
                    ]
                ]));
        }
        
        return redirect()
            ->route('properties.show', $property)
            ->with('success', 'Property updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Property $property)
    {
        $this->authorize('delete', $property);
        
        // Check if property has units
        if ($property->units()->exists()) {
            if (request()->header('HX-Request')) {
                return response()
                    ->make('Cannot delete property with existing units', 422)
                    ->header('HX-Trigger', json_encode([
                        'toast' => [
                            'message' => 'Cannot delete property with existing units',
                            'type' => 'error'
                        ]
                    ]));
            }
            
            return redirect()
                ->route('properties.index')
                ->with('error', 'Cannot delete property with existing units');
        }
        
        $property->delete();
        
        if (request()->header('HX-Request')) {
            return response('')
                ->header('HX-Trigger', json_encode([
                    'toast' => [
                        'message' => 'Property deleted successfully',
                        'type' => 'success'
                    ]
                ]));
        }
        
        return redirect()
            ->route('properties.index')
            ->with('success', 'Property deleted successfully');
    }
}