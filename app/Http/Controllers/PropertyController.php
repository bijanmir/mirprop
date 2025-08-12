<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PropertyController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Property::class, 'property');
    }

    public function index(Request $request)
    {
        $query = Property::with(['units' => function ($query) {
                $query->select('id', 'property_id', 'status', 'rent_amount_cents');
            }])
            ->withCount(['units', 'units as occupied_units_count' => function ($query) {
                $query->where('status', 'occupied');
            }]);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('address_line1', 'ilike', "%{$search}%")
                  ->orWhere('city', 'ilike', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Sorting
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        
        $allowedSorts = ['name', 'type', 'city', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $properties = $query->paginate(15)->withQueryString();

        // Add calculated fields
        $properties->getCollection()->transform(function ($property) {
            $property->monthly_revenue = $property->units->sum('rent_amount_cents');
            return $property;
        });

        // Return HTMX partial or full page
        if ($request->header('HX-Request')) {
            return view('properties.partials.table', compact('properties'));
        }

        return view('properties.index', compact('properties'));
    }

    public function create()
    {
        return view('properties.create');
    }

    public function store(StorePropertyRequest $request)
    {
        $property = Property::create($request->validated());

        if ($request->header('HX-Request')) {
            return response()
                ->view('properties.partials.created', compact('property'))
                ->header('HX-Trigger', json_encode([
                    'toast' => [
                        'message' => 'Property created successfully',
                        'type' => 'success'
                    ],
                    'propertiesRefresh' => true
                ]));
        }

        return redirect()
            ->route('properties.show', $property)
            ->with('success', 'Property created successfully');
    }

    public function show(Property $property)
    {
        $property->load([
            'units' => function ($query) {
                $query->with(['activeLease.primaryContact', 'maintenanceTickets' => function ($q) {
                    $q->whereIn('status', ['open', 'in_progress'])->limit(3);
                }]);
            }
        ]);

        // Calculate metrics
        $metrics = [
            'total_units' => $property->units->count(),
            'occupied_units' => $property->units->where('status', 'occupied')->count(),
            'available_units' => $property->units->where('status', 'available')->count(),
            'maintenance_units' => $property->units->where('status', 'maintenance')->count(),
            'monthly_revenue' => $property->units->sum('rent_amount_cents'),
            'occupancy_rate' => $property->units->count() > 0 
                ? round(($property->units->where('status', 'occupied')->count() / $property->units->count()) * 100, 1)
                : 0,
        ];

        $recentActivity = collect(); // TODO: Implement activity log

        return view('properties.show', compact('property', 'metrics', 'recentActivity'));
    }

    public function edit(Property $property)
    {
        if (request()->header('HX-Request')) {
            return view('properties.partials.edit-form', compact('property'));
        }

        return view('properties.edit', compact('property'));
    }

    public function update(UpdatePropertyRequest $request, Property $property)
    {
        $property->update($request->validated());

        if ($request->header('HX-Request')) {
            return response()
                ->view('properties.partials.updated', compact('property'))
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

    public function destroy(Property $property)
    {
        // Check if property has units
        if ($property->units()->count() > 0) {
            if (request()->header('HX-Request')) {
                return response()
                    ->json(['error' => 'Cannot delete property with existing units'])
                    ->setStatusCode(422);
            }

            return redirect()
                ->route('properties.index')
                ->with('error', 'Cannot delete property with existing units. Please remove all units first.');
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