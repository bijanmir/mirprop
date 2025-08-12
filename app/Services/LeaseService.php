<?php

namespace App\Services;

use App\Models\Lease;
use App\Models\LeaseCharge;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaseService
{
    public function create(array $data): Lease
    {
        return DB::transaction(function () use ($data) {
            // Create the lease
            $lease = Lease::create($data);
            
            // Create initial rent charge
            $this->createRentCharge($lease, $data);
            
            // Create deposit charge if applicable
            if ($lease->deposit_amount_cents > 0) {
                $this->createDepositCharge($lease);
            }
            
            // Update unit status
            $lease->unit->update(['status' => 'occupied']);
            
            Log::info('Lease created successfully', [
                'lease_id' => $lease->id,
                'unit_id' => $lease->unit_id,
                'tenant_id' => $lease->primary_contact_id,
            ]);
            
            return $lease;
        });
    }
    
    public function update(Lease $lease, array $data): Lease
    {
        return DB::transaction(function () use ($lease, $data) {
            $lease->update($data);
            
            // Update recurring rent charge if amount changed
            if (isset($data['rent_amount_cents'])) {
                $this->updateRentCharges($lease, $data['rent_amount_cents']);
            }
            
            Log::info('Lease updated successfully', [
                'lease_id' => $lease->id,
                'updated_fields' => array_keys($data),
            ]);
            
            return $lease;
        });
    }
    
    public function delete(Lease $lease): bool
    {
        return DB::transaction(function () use ($lease) {
            // Update unit status if lease is active
            if ($lease->status === 'active') {
                $lease->unit->update(['status' => 'available']);
            }
            
            $result = $lease->delete();
            
            Log::info('Lease deleted', ['lease_id' => $lease->id]);
            
            return $result;
        });
    }
    
    public function paginate(array $filters = [], int $perPage = 15)
    {
        $query = Lease::with(['unit.property', 'primaryContact']);
        
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('unit.property', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('primaryContact', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['property_id'])) {
            $query->whereHas('unit', fn($q) => $q->where('property_id', $filters['property_id']));
        }
        
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
    
    protected function createRentCharge(Lease $lease, array $data): LeaseCharge
    {
        return $lease->charges()->create([
            'type' => 'rent',
            'amount_cents' => $lease->rent_amount_cents,
            'description' => 'Monthly Rent',
            'due_date' => $lease->start_date->day($data['rent_due_day'] ?? 1),
            'balance_cents' => $lease->rent_amount_cents,
            'is_recurring' => true,
            'day_of_month' => $data['rent_due_day'] ?? 1,
        ]);
    }
    
    protected function createDepositCharge(Lease $lease): LeaseCharge
    {
        return $lease->charges()->create([
            'type' => 'deposit',
            'amount_cents' => $lease->deposit_amount_cents,
            'description' => 'Security Deposit',
            'due_date' => $lease->start_date,
            'balance_cents' => $lease->deposit_amount_cents,
            'is_recurring' => false,
        ]);
    }
    
    protected function updateRentCharges(Lease $lease, int $newAmountCents): void
    {
        $lease->charges()
            ->where('type', 'rent')
            ->where('is_recurring', true)
            ->update(['amount_cents' => $newAmountCents]);
    }
}