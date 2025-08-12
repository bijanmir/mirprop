<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyService
{
    public function create(array $data): Property
    {
        $property = Property::create($data);
        
        Log::info('Property created', [
            'property_id' => $property->id,
            'name' => $property->name,
            'type' => $property->type,
        ]);
        
        return $property;
    }
    
    public function update(Property $property, array $data): Property
    {
        $property->update($data);
        
        Log::info('Property updated', [
            'property_id' => $property->id,
            'updated_fields' => array_keys($data),
        ]);
        
        return $property;
    }
    
    public function delete(Property $property): bool
    {
        if ($property->units()->exists()) {
            throw new \Exception('Cannot delete property with existing units');
        }
        
        $result = $property->delete();
        
        Log::info('Property deleted', ['property_id' => $property->id]);
        
        return $result;
    }
    
    public function paginate(array $filters = [], int $perPage = 15)
    {
        $query = Property::withCount([
            'units',
            'units as occupied_units_count' => function ($query) {
                $query->where('status', 'occupied');
            }
        ]);
        
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address_line1', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }
        
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        $sort = $filters['sort'] ?? 'name';
        $direction = $filters['direction'] ?? 'asc';
        
        return $query->orderBy($sort, $direction)->paginate($perPage);
    }
    
    public function getMetrics(Property $property): array
    {
        $units = $property->units;
        
        return [
            'total_units' => $units->count(),
            'occupied_units' => $units->where('status', 'occupied')->count(),
            'vacant_units' => $units->where('status', 'available')->count(),
            'monthly_rent' => $units->sum('rent_amount_cents'),
            'occupancy_rate' => $units->count() > 0 
                ? round(($units->where('status', 'occupied')->count() / $units->count()) * 100, 1)
                : 0,
        ];
    }
}